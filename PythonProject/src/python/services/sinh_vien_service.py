from typing import Any, Dict, List, Optional
from uuid import uuid4

from core.database import db_cursor, db_transaction


def _new_id() -> str:
    return uuid4().hex


def _normalize_gender(value: Any) -> Optional[int]:
    if value is None or value == "":
        return None
    return 1 if int(value) == 1 else 0


def _get_or_create_role(cursor, ten_vai_tro: str) -> str:
    cursor.execute(
        "SELECT vai_tro_id FROM vai_tro WHERE ten_vai_tro = %s LIMIT 1",
        (ten_vai_tro,),
    )
    role = cursor.fetchone()
    if role:
        return role["vai_tro_id"]

    role_id = _new_id()
    cursor.execute(
        "INSERT INTO vai_tro (vai_tro_id, ten_vai_tro, mo_ta) VALUES (%s, %s, %s)",
        (role_id, ten_vai_tro, f"Vai tro {ten_vai_tro}"),
    )
    return role_id


def list_students(keyword: str = "", lop_id: str = "") -> List[Dict[str, Any]]:
    query = """
        SELECT sv.sinh_vien_id, sv.tai_khoan_id, sv.msv, sv.ten_sv, sv.gioi_tinh,
               sv.ngay_sinh, sv.lop_id, l.ten_lop, tk.email
        FROM sinh_vien sv
        JOIN lop l ON l.lop_id = sv.lop_id
        JOIN tai_khoan tk ON tk.tai_khoan_id = sv.tai_khoan_id
        WHERE 1 = 1
    """
    params: List[Any] = []

    if keyword:
        query += " AND (sv.msv LIKE %s OR sv.ten_sv LIKE %s OR tk.email LIKE %s)"
        like_kw = f"%{keyword}%"
        params.extend([like_kw, like_kw, like_kw])

    if lop_id:
        query += " AND sv.lop_id = %s"
        params.append(lop_id)

    query += " ORDER BY sv.ten_sv ASC"

    with db_cursor() as (_, cursor):
        cursor.execute(query, tuple(params))
        return cursor.fetchall()


def get_student(sinh_vien_id: str) -> Optional[Dict[str, Any]]:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT sv.sinh_vien_id, sv.tai_khoan_id, sv.msv, sv.ten_sv, sv.gioi_tinh,
                   sv.ngay_sinh, sv.lop_id, tk.email
            FROM sinh_vien sv
            JOIN tai_khoan tk ON tk.tai_khoan_id = sv.tai_khoan_id
            WHERE sv.sinh_vien_id = %s
            LIMIT 1
            """,
            (sinh_vien_id,),
        )
        return cursor.fetchone()


def get_student_by_account(tai_khoan_id: str) -> Optional[Dict[str, Any]]:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT sv.sinh_vien_id, sv.tai_khoan_id, sv.msv, sv.ten_sv, sv.gioi_tinh,
                   sv.ngay_sinh, sv.lop_id, tk.email
            FROM sinh_vien sv
            JOIN tai_khoan tk ON tk.tai_khoan_id = sv.tai_khoan_id
            WHERE sv.tai_khoan_id = %s
            LIMIT 1
            """,
            (tai_khoan_id,),
        )
        return cursor.fetchone()


def create_student_for_account(tai_khoan_id: str, payload: Dict[str, Any]) -> Dict[str, Any]:
    required = ["msv", "ten_sv", "lop_id"]
    for field in required:
        if not payload.get(field):
            raise ValueError(f"Thieu truong bat buoc: {field}")

    with db_transaction() as (_, cursor):
        cursor.execute(
            "SELECT tai_khoan_id, email, vai_tro_id FROM tai_khoan WHERE tai_khoan_id = %s LIMIT 1",
            (tai_khoan_id,),
        )
        account = cursor.fetchone()
        if not account:
            raise ValueError("Khong tim thay tai khoan")

        cursor.execute(
            "SELECT sinh_vien_id FROM sinh_vien WHERE tai_khoan_id = %s LIMIT 1",
            (tai_khoan_id,),
        )
        existing = cursor.fetchone()
        if existing:
            raise ValueError("Tai khoan da co thong tin sinh vien")

        role_id = _get_or_create_role(cursor, "SINH_VIEN")
        if account["vai_tro_id"] != role_id:
            cursor.execute(
                "UPDATE tai_khoan SET vai_tro_id = %s WHERE tai_khoan_id = %s",
                (role_id, tai_khoan_id),
            )

        if payload.get("email"):
            cursor.execute(
                "UPDATE tai_khoan SET email = %s WHERE tai_khoan_id = %s",
                (payload["email"], tai_khoan_id),
            )

        sinh_vien_id = _new_id()
        cursor.execute(
            """
            INSERT INTO sinh_vien (
                sinh_vien_id, tai_khoan_id, msv, ten_sv, gioi_tinh, ngay_sinh, lop_id
            ) VALUES (%s, %s, %s, %s, %s, %s, %s)
            """,
            (
                sinh_vien_id,
                tai_khoan_id,
                payload["msv"],
                payload["ten_sv"],
                _normalize_gender(payload.get("gioi_tinh")),
                payload.get("ngay_sinh"),
                payload["lop_id"],
            ),
        )

    return get_student(sinh_vien_id) or {}


def create_student(payload: Dict[str, Any]) -> Dict[str, Any]:
    required = ["msv", "ten_sv", "lop_id", "email"]
    for field in required:
        if not payload.get(field):
            raise ValueError(f"Thieu truong bat buoc: {field}")

    sinh_vien_id = _new_id()
    tai_khoan_id = _new_id()
    password = payload.get("mat_khau") or "123456"

    with db_transaction() as (_, cursor):
        vai_tro_id = _get_or_create_role(cursor, "SINH_VIEN")

        cursor.execute(
            """
            INSERT INTO tai_khoan (tai_khoan_id, email, mat_khau, vai_tro_id, is_active)
            VALUES (%s, %s, %s, %s, 1)
            """,
            (tai_khoan_id, payload["email"], password, vai_tro_id),
        )

        cursor.execute(
            """
            INSERT INTO sinh_vien (
                sinh_vien_id, tai_khoan_id, msv, ten_sv, gioi_tinh, ngay_sinh, lop_id
            ) VALUES (%s, %s, %s, %s, %s, %s, %s)
            """,
            (
                sinh_vien_id,
                tai_khoan_id,
                payload["msv"],
                payload["ten_sv"],
                _normalize_gender(payload.get("gioi_tinh")),
                payload.get("ngay_sinh"),
                payload["lop_id"],
            ),
        )

    return get_student(sinh_vien_id) or {}


def update_student(sinh_vien_id: str, payload: Dict[str, Any]) -> Dict[str, Any]:
    current = get_student(sinh_vien_id)
    if not current:
        raise ValueError("Khong tim thay sinh vien")

    with db_transaction() as (_, cursor):
        cursor.execute(
            """
            UPDATE sinh_vien
            SET msv = %s,
                ten_sv = %s,
                gioi_tinh = %s,
                ngay_sinh = %s,
                lop_id = %s
            WHERE sinh_vien_id = %s
            """,
            (
                payload.get("msv", current["msv"]),
                payload.get("ten_sv", current["ten_sv"]),
                _normalize_gender(payload.get("gioi_tinh", current.get("gioi_tinh"))),
                payload.get("ngay_sinh", current.get("ngay_sinh")),
                payload.get("lop_id", current["lop_id"]),
                sinh_vien_id,
            ),
        )

        if payload.get("email"):
            cursor.execute(
                "UPDATE tai_khoan SET email = %s WHERE tai_khoan_id = %s",
                (payload["email"], current["tai_khoan_id"]),
            )

    return get_student(sinh_vien_id) or {}


def transfer_student_class(sinh_vien_id: str, lop_id_moi: str) -> Dict[str, Any]:
    with db_transaction() as (_, cursor):
        cursor.execute(
            "SELECT sinh_vien_id FROM sinh_vien WHERE sinh_vien_id = %s LIMIT 1",
            (sinh_vien_id,),
        )
        exists = cursor.fetchone()
        if not exists:
            raise ValueError("Khong tim thay sinh vien de chuyen lop")

        cursor.execute(
            "UPDATE sinh_vien SET lop_id = %s WHERE sinh_vien_id = %s",
            (lop_id_moi, sinh_vien_id),
        )

    return get_student(sinh_vien_id) or {}


def delete_student(sinh_vien_id: str) -> None:
    current = get_student(sinh_vien_id)
    if not current:
        raise ValueError("Khong tim thay sinh vien")

    with db_transaction() as (_, cursor):
        cursor.execute("SELECT ds_lhp_id FROM ds_lhp WHERE sinh_vien_id = %s", (sinh_vien_id,))
        enrollments = cursor.fetchall()
        ds_ids = [row["ds_lhp_id"] for row in enrollments]

        if ds_ids:
            placeholder = ", ".join(["%s"] * len(ds_ids))
            cursor.execute(
                f"DELETE FROM lich_su_hoc_mon WHERE ds_lhp_id IN ({placeholder})",
                tuple(ds_ids),
            )
            cursor.execute(
                f"DELETE FROM ds_lhp WHERE ds_lhp_id IN ({placeholder})",
                tuple(ds_ids),
            )

        cursor.execute("DELETE FROM sinh_vien WHERE sinh_vien_id = %s", (sinh_vien_id,))
        cursor.execute(
            "DELETE FROM tai_khoan WHERE tai_khoan_id = %s",
            (current["tai_khoan_id"],),
        )


def list_lop() -> List[Dict[str, Any]]:
    with db_cursor() as (_, cursor):
        cursor.execute("SELECT lop_id, ten_lop FROM lop ORDER BY ten_lop")
        return cursor.fetchall()


def list_giang_vien() -> List[Dict[str, Any]]:
    with db_cursor() as (_, cursor):
        cursor.execute(
            "SELECT giang_vien_id, ma_gv, ten_gv FROM giang_vien ORDER BY ten_gv"
        )
        return cursor.fetchall()


def list_mon_hoc() -> List[Dict[str, Any]]:
    with db_cursor() as (_, cursor):
        cursor.execute(
            "SELECT mon_hoc_id, ma_mon, ten_mon, so_tin_chi FROM mon_hoc ORDER BY ten_mon"
        )
        return cursor.fetchall()


def list_hoc_ky() -> List[Dict[str, Any]]:
    with db_cursor() as (_, cursor):
        cursor.execute(
            "SELECT hoc_ky_id, ma_hoc_ky, ten_hoc_ky, is_hien_tai FROM hoc_ky ORDER BY ten_hoc_ky"
        )
        return cursor.fetchall()


def list_lop_hoc_phan() -> List[Dict[str, Any]]:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT lhp.lhp_id, lhp.ma_lhp, lhp.giang_vien_id, gv.ten_gv,
                   lhp.mon_hoc_id, mh.ten_mon, lhp.hoc_ky_id, hk.ten_hoc_ky,
                   lhp.ty_le_cc, lhp.ty_le_gk, lhp.ty_le_ck,
                   (SELECT COUNT(*) FROM ds_lhp ds WHERE ds.lhp_id = lhp.lhp_id) AS so_luong_sv
            FROM lop_hoc_phan lhp
            JOIN giang_vien gv ON gv.giang_vien_id = lhp.giang_vien_id
            JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
            JOIN hoc_ky hk ON hk.hoc_ky_id = lhp.hoc_ky_id
            ORDER BY hk.ten_hoc_ky DESC, lhp.ma_lhp ASC
            """
        )
        return cursor.fetchall()


def create_lop_hoc_phan(payload: Dict[str, Any]) -> Dict[str, Any]:
    required = ["ma_lhp", "giang_vien_id", "mon_hoc_id", "hoc_ky_id"]
    for field in required:
        if not payload.get(field):
            raise ValueError(f"Thieu truong bat buoc: {field}")

    ty_le_cc = int(payload.get("ty_le_cc", 10))
    ty_le_gk = int(payload.get("ty_le_gk", 30))
    ty_le_ck = int(payload.get("ty_le_ck", 60))

    if ty_le_cc + ty_le_gk + ty_le_ck != 100:
        raise ValueError("Tong ty le diem thanh phan phai bang 100")

    lhp_id = _new_id()
    with db_transaction() as (_, cursor):
        cursor.execute(
            """
            INSERT INTO lop_hoc_phan (
                lhp_id, ma_lhp, giang_vien_id, mon_hoc_id, hoc_ky_id,
                ty_le_cc, ty_le_gk, ty_le_ck, trang_thai_giao_vu, trang_thai_giang_vien
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, 1, 0)
            """,
            (
                lhp_id,
                payload["ma_lhp"],
                payload["giang_vien_id"],
                payload["mon_hoc_id"],
                payload["hoc_ky_id"],
                ty_le_cc,
                ty_le_gk,
                ty_le_ck,
            ),
        )

    with db_cursor() as (_, cursor):
        cursor.execute("SELECT * FROM lop_hoc_phan WHERE lhp_id = %s", (lhp_id,))
        return cursor.fetchone() or {}


def assign_lecturer(lhp_id: str, giang_vien_id: str) -> Dict[str, Any]:
    with db_transaction() as (_, cursor):
        cursor.execute(
            "SELECT lhp_id FROM lop_hoc_phan WHERE lhp_id = %s LIMIT 1",
            (lhp_id,),
        )
        exists = cursor.fetchone()
        if not exists:
            raise ValueError("Khong tim thay lop hoc phan")

        cursor.execute(
            "UPDATE lop_hoc_phan SET giang_vien_id = %s WHERE lhp_id = %s",
            (giang_vien_id, lhp_id),
        )

    with db_cursor() as (_, cursor):
        cursor.execute("SELECT * FROM lop_hoc_phan WHERE lhp_id = %s", (lhp_id,))
        return cursor.fetchone() or {}


def add_student_to_lhp(lhp_id: str, sinh_vien_id: str) -> Dict[str, Any]:
    ds_lhp_id = _new_id()

    with db_transaction() as (_, cursor):
        cursor.execute(
            """
            SELECT lhp.mon_hoc_id
            FROM lop_hoc_phan lhp
            WHERE lhp.lhp_id = %s
            LIMIT 1
            """,
            (lhp_id,),
        )
        lhp = cursor.fetchone()
        if not lhp:
            raise ValueError("Lop hoc phan khong ton tai")

        cursor.execute(
            """
            INSERT INTO ds_lhp (ds_lhp_id, lhp_id, sinh_vien_id, diem_cc, diem_gk, diem_ck, diem_tong)
            VALUES (%s, %s, %s, NULL, NULL, NULL, NULL)
            """,
            (ds_lhp_id, lhp_id, sinh_vien_id),
        )

        cursor.execute(
            """
            SELECT COALESCE(MAX(lan_hoc), 0) + 1 AS lan_hoc
            FROM lich_su_hoc_mon
            WHERE sinh_vien_id = %s AND mon_hoc_id = %s
            """,
            (sinh_vien_id, lhp["mon_hoc_id"]),
        )
        lan_hoc = cursor.fetchone()["lan_hoc"]

        cursor.execute(
            """
            INSERT INTO lich_su_hoc_mon (lich_su_id, sinh_vien_id, mon_hoc_id, ds_lhp_id, lan_hoc)
            VALUES (%s, %s, %s, %s, %s)
            """,
            (_new_id(), sinh_vien_id, lhp["mon_hoc_id"], ds_lhp_id, lan_hoc),
        )

    return {
        "ds_lhp_id": ds_lhp_id,
        "lhp_id": lhp_id,
        "sinh_vien_id": sinh_vien_id,
        "status": "enrolled",
    }


def remove_student_from_lhp(lhp_id: str, sinh_vien_id: str) -> Dict[str, Any]:
    with db_transaction() as (_, cursor):
        cursor.execute(
            "SELECT ds_lhp_id FROM ds_lhp WHERE lhp_id = %s AND sinh_vien_id = %s LIMIT 1",
            (lhp_id, sinh_vien_id),
        )
        enrollment = cursor.fetchone()
        if not enrollment:
            raise ValueError("Sinh vien khong nam trong lop hoc phan")

        ds_lhp_id = enrollment["ds_lhp_id"]
        cursor.execute("DELETE FROM lich_su_hoc_mon WHERE ds_lhp_id = %s", (ds_lhp_id,))
        cursor.execute("DELETE FROM ds_lhp WHERE ds_lhp_id = %s", (ds_lhp_id,))

    return {
        "ds_lhp_id": ds_lhp_id,
        "lhp_id": lhp_id,
        "sinh_vien_id": sinh_vien_id,
        "status": "removed",
    }


def list_lhp_enrollments(lhp_id: str) -> List[Dict[str, Any]]:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT ds.ds_lhp_id, sv.sinh_vien_id, sv.msv, sv.ten_sv, l.ten_lop
            FROM ds_lhp ds
            JOIN sinh_vien sv ON sv.sinh_vien_id = ds.sinh_vien_id
            JOIN lop l ON l.lop_id = sv.lop_id
            WHERE ds.lhp_id = %s
            ORDER BY sv.ten_sv
            """,
            (lhp_id,),
        )
        return cursor.fetchall()
