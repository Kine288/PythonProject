from __future__ import annotations

import hashlib
import json
import uuid
from typing import Dict, List, Optional

from core.database import db_cursor, execute_transaction
from services.gpa_service import br2_quy_doi


def _new_id() -> str:
    return uuid.uuid4().hex


def _hash_password(raw: str) -> str:
    return hashlib.sha256(raw.encode("utf-8")).hexdigest()


def _to_bool_or_none(value):
    if value is None or value == "":
        return None
    return bool(int(value)) if isinstance(value, str) and value.isdigit() else bool(value)


def list_sinh_vien(keyword: str = "", lop_id: str = "", trang_thai: str = "") -> List[Dict]:
    sql = """
        SELECT sv.sinh_vien_id, sv.msv, sv.ho_ten, sv.ngay_sinh, sv.gioi_tinh,
               sv.trang_thai, sv.lop_id, lsh.ma_lop, lsh.ten_lop
        FROM sinh_vien sv
        LEFT JOIN lop_sinh_hoat lsh ON lsh.lop_id = sv.lop_id
        WHERE 1 = 1
    """
    params: List = []
    if keyword:
        kw = f"%{keyword}%"
        sql += " AND (sv.msv LIKE %s OR sv.ho_ten LIKE %s)"
        params.extend([kw, kw])
    if lop_id:
        sql += " AND sv.lop_id = %s"
        params.append(lop_id)
    if trang_thai:
        sql += " AND sv.trang_thai = %s"
        params.append(trang_thai)

    sql += " ORDER BY sv.msv ASC"

    with db_cursor() as (_, cursor):
        cursor.execute(sql, tuple(params))
        return cursor.fetchall()


def get_sinh_vien(sinh_vien_id: str) -> Optional[Dict]:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT sv.*, tk.email AS dang_nhap, tk.vai_tro, tk.is_active,
                   lsh.ma_lop, lsh.ten_lop
            FROM sinh_vien sv
            JOIN tai_khoan tk ON tk.tai_khoan_id = sv.tai_khoan_id
            LEFT JOIN lop_sinh_hoat lsh ON lsh.lop_id = sv.lop_id
            WHERE sv.sinh_vien_id = %s
            LIMIT 1
            """,
            (sinh_vien_id,),
        )
        return cursor.fetchone()


def create_sinh_vien_admin(payload: Dict) -> Dict:
    msv = (payload.get("msv") or "").strip()
    ho_ten = (payload.get("ho_ten") or "").strip()
    lop_id = (payload.get("lop_id") or "").strip()
    ngay_sinh = payload.get("ngay_sinh")
    gioi_tinh = payload.get("gioi_tinh")
    admin_id = (payload.get("admin_tai_khoan_id") or "").strip()

    if not msv or not ho_ten or not lop_id:
        raise ValueError("Thieu truong bat buoc: msv, ho_ten, lop_id")

    tai_khoan_id = _new_id()
    sinh_vien_id = _new_id()
    raw_password = (payload.get("mat_khau") or msv).strip()

    def _tx(cursor):
        cursor.execute(
            """
            INSERT INTO tai_khoan (tai_khoan_id, email, mat_khau_hash, vai_tro, is_active)
            VALUES (%s, %s, %s, 'SINH_VIEN', TRUE)
            """,
            (tai_khoan_id, msv, _hash_password(raw_password)),
        )

        cursor.execute(
            """
            INSERT INTO sinh_vien (sinh_vien_id, tai_khoan_id, msv, ho_ten, ngay_sinh, gioi_tinh, lop_id, trang_thai)
            VALUES (%s, %s, %s, %s, %s, %s, %s, 'DANG_HOC')
            """,
            (sinh_vien_id, tai_khoan_id, msv, ho_ten, ngay_sinh, gioi_tinh, lop_id),
        )

        if admin_id:
            cursor.execute(
                """
                INSERT INTO admin_log (log_id, tai_khoan_id, hanh_dong, doi_tuong_loai, doi_tuong_id, du_lieu)
                VALUES (%s, %s, %s, %s, %s, %s)
                """,
                (
                    _new_id(),
                    admin_id,
                    "CREATE_STUDENT_ACCOUNT",
                    "SINH_VIEN",
                    sinh_vien_id,
                    json.dumps({"msv": msv, "tai_khoan": msv}),
                ),
            )

    execute_transaction(_tx)
    result = get_sinh_vien(sinh_vien_id)
    return result or {"sinh_vien_id": sinh_vien_id}


def update_sinh_vien_hoc_vu(sinh_vien_id: str, payload: Dict) -> Dict:
    ho_ten = payload.get("ho_ten")
    ngay_sinh = payload.get("ngay_sinh")
    gioi_tinh = payload.get("gioi_tinh")
    lop_id = payload.get("lop_id")
    trang_thai = payload.get("trang_thai")
    nguoi_thay_doi = (payload.get("nguoi_thay_doi") or "").strip()

    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT sinh_vien_id, ho_ten, ngay_sinh, gioi_tinh, lop_id, trang_thai
            FROM sinh_vien
            WHERE sinh_vien_id = %s
            LIMIT 1
            """,
            (sinh_vien_id,),
        )
        old = cursor.fetchone()
        if old is None:
            raise ValueError("Khong tim thay sinh vien")

    new_ho_ten = ho_ten if ho_ten is not None else old["ho_ten"]
    new_ngay_sinh = ngay_sinh if ngay_sinh is not None else old["ngay_sinh"]
    new_gioi_tinh = gioi_tinh if gioi_tinh is not None else old["gioi_tinh"]
    new_lop_id = lop_id if lop_id is not None else old["lop_id"]
    new_trang_thai = trang_thai if trang_thai is not None else old["trang_thai"]

    def _tx(cursor):
        cursor.execute(
            """
            UPDATE sinh_vien
            SET ho_ten = %s,
                ngay_sinh = %s,
                gioi_tinh = %s,
                lop_id = %s,
                trang_thai = %s
            WHERE sinh_vien_id = %s
            """,
            (new_ho_ten, new_ngay_sinh, new_gioi_tinh, new_lop_id, new_trang_thai, sinh_vien_id),
        )

        if nguoi_thay_doi:
            cursor.execute(
                """
                INSERT INTO lich_su_ho_so (ls_hs_id, sinh_vien_id, nguoi_thay_doi, truoc_thay_doi, sau_thay_doi)
                VALUES (%s, %s, %s, %s, %s)
                """,
                (
                    _new_id(),
                    sinh_vien_id,
                    nguoi_thay_doi,
                    json.dumps(
                        {
                            "ho_ten": old["ho_ten"],
                            "ngay_sinh": str(old["ngay_sinh"]) if old["ngay_sinh"] is not None else None,
                            "gioi_tinh": old["gioi_tinh"],
                            "lop_id": old["lop_id"],
                            "trang_thai": old["trang_thai"],
                        }
                    ),
                    json.dumps(
                        {
                            "ho_ten": new_ho_ten,
                            "ngay_sinh": str(new_ngay_sinh) if new_ngay_sinh is not None else None,
                            "gioi_tinh": new_gioi_tinh,
                            "lop_id": new_lop_id,
                            "trang_thai": new_trang_thai,
                            "ly_do": payload.get("ly_do"),
                        }
                    ),
                ),
            )

    execute_transaction(_tx)
    return get_sinh_vien(sinh_vien_id) or {}


def delete_sinh_vien(sinh_vien_id: str, nguoi_thay_doi: str = "") -> Dict:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT sv.sinh_vien_id, sv.tai_khoan_id, sv.msv
            FROM sinh_vien sv
            WHERE sv.sinh_vien_id = %s
            LIMIT 1
            """,
            (sinh_vien_id,),
        )
        sv = cursor.fetchone()
        if sv is None:
            raise ValueError("Khong tim thay sinh vien")

        cursor.execute(
            "SELECT COUNT(*) AS total FROM ds_lhp WHERE sinh_vien_id = %s",
            (sinh_vien_id,),
        )
        in_lhp = int((cursor.fetchone() or {"total": 0})["total"] or 0)
        if in_lhp > 0:
            raise ValueError("Sinh vien da co lich su hoc tap/LHP, khong the xoa")

    tai_khoan_id = sv["tai_khoan_id"]
    msv = sv["msv"]

    def _tx(cursor):
        cursor.execute("DELETE FROM lich_su_ho_so WHERE sinh_vien_id = %s", (sinh_vien_id,))
        cursor.execute("DELETE FROM ket_qua_hoc_ky WHERE sinh_vien_id = %s", (sinh_vien_id,))
        cursor.execute("DELETE FROM sinh_vien WHERE sinh_vien_id = %s", (sinh_vien_id,))
        cursor.execute("DELETE FROM tai_khoan WHERE tai_khoan_id = %s", (tai_khoan_id,))

        if nguoi_thay_doi:
            cursor.execute(
                """
                INSERT INTO admin_log (log_id, tai_khoan_id, hanh_dong, doi_tuong_loai, doi_tuong_id, du_lieu)
                VALUES (%s, %s, %s, %s, %s, %s)
                """,
                (
                    _new_id(),
                    nguoi_thay_doi,
                    "DELETE_STUDENT_ACCOUNT",
                    "SINH_VIEN",
                    sinh_vien_id,
                    json.dumps({"msv": msv}),
                ),
            )

    execute_transaction(_tx)
    return {"success": True, "sinh_vien_id": sinh_vien_id}


def bang_diem_sinh_vien(sinh_vien_id: str, hoc_ky_id: str = "") -> Dict:
    sql = """
        SELECT mh.ma_mon, mh.ten_mon, mh.so_tin_chi,
               ds.diem_cc, ds.diem_gk, ds.diem_ck, ds.diem_tong,
               hk.hoc_ky_id, hk.ten_hoc_ky
        FROM ds_lhp ds
        JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
        JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
        JOIN hoc_ky hk ON hk.hoc_ky_id = lhp.hoc_ky_id
        WHERE ds.sinh_vien_id = %s
          AND lhp.trang_thai = 'DA_DUYET'
    """
    params: List = [sinh_vien_id]
    if hoc_ky_id:
        sql += " AND hk.hoc_ky_id = %s"
        params.append(hoc_ky_id)
    sql += " ORDER BY hk.ten_hoc_ky DESC, mh.ma_mon ASC"

    with db_cursor() as (_, cursor):
        cursor.execute(sql, tuple(params))
        rows = cursor.fetchall()

        for row in rows:
            if row["diem_tong"] is None:
                row["diem_chu"] = None
                row["diem_he_4"] = None
            else:
                diem_chu, diem_he4 = br2_quy_doi(row["diem_tong"])
                row["diem_chu"] = diem_chu
                row["diem_he_4"] = float(diem_he4)

        cursor.execute(
            """
            SELECT *
            FROM ket_qua_hoc_ky
            WHERE sinh_vien_id = %s
            """ + (" AND hoc_ky_id = %s" if hoc_ky_id else "") + " ORDER BY hoc_ky_id DESC",
            tuple([sinh_vien_id] + ([hoc_ky_id] if hoc_ky_id else [])),
        )
        summary = cursor.fetchall()

    return {"bang_diem": rows, "tong_ket": summary}


def list_tai_khoan(keyword: str = "", vai_tro: str = "", is_active=None) -> List[Dict]:
    sql = """
        SELECT tk.tai_khoan_id, tk.email, tk.vai_tro, tk.is_active, tk.lan_dang_nhap_cuoi,
               sv.ho_ten AS ten_sinh_vien,
               gv.ho_ten AS ten_giang_vien,
               gvu.ho_ten AS ten_giao_vu
        FROM tai_khoan tk
        LEFT JOIN sinh_vien sv ON sv.tai_khoan_id = tk.tai_khoan_id
        LEFT JOIN giang_vien gv ON gv.tai_khoan_id = tk.tai_khoan_id
        LEFT JOIN giao_vu gvu ON gvu.tai_khoan_id = tk.tai_khoan_id
        WHERE 1 = 1
    """
    params: List = []

    if keyword:
        kw = f"%{keyword}%"
        sql += " AND (tk.email LIKE %s OR sv.ho_ten LIKE %s OR gv.ho_ten LIKE %s OR gvu.ho_ten LIKE %s)"
        params.extend([kw, kw, kw, kw])
    if vai_tro:
        sql += " AND tk.vai_tro = %s"
        params.append(vai_tro)
    if is_active is not None:
        sql += " AND tk.is_active = %s"
        params.append(1 if bool(is_active) else 0)

    sql += " ORDER BY tk.ngay_tao DESC"

    with db_cursor() as (_, cursor):
        cursor.execute(sql, tuple(params))
        return cursor.fetchall()


def khoa_tai_khoan(tai_khoan_id: str, active: bool) -> Dict:
    def _tx(cursor):
        cursor.execute(
            "UPDATE tai_khoan SET is_active = %s WHERE tai_khoan_id = %s",
            (1 if active else 0, tai_khoan_id),
        )
        if cursor.rowcount == 0:
            raise ValueError("Khong tim thay tai khoan")

    execute_transaction(_tx)
    return {"success": True}


def reset_mat_khau(tai_khoan_id: str, new_password: str = "123456") -> Dict:
    execute_transaction(
        lambda cursor: cursor.execute(
            "UPDATE tai_khoan SET mat_khau_hash = %s WHERE tai_khoan_id = %s",
            (_hash_password(new_password), tai_khoan_id),
        )
    )
    return {"success": True}


def doi_vai_tro(tai_khoan_id: str, vai_tro_moi: str) -> Dict:
    allowed = {"ADMIN", "GIAO_VU", "GIANG_VIEN", "SINH_VIEN"}
    if vai_tro_moi not in allowed:
        raise ValueError("Vai tro khong hop le")

    execute_transaction(
        lambda cursor: cursor.execute(
            "UPDATE tai_khoan SET vai_tro = %s WHERE tai_khoan_id = %s",
            (vai_tro_moi, tai_khoan_id),
        )
    )
    return {"success": True}


def list_lhp(hoc_ky_id: str = "", giang_vien_id: str = "") -> List[Dict]:
    sql = """
        SELECT lhp.lhp_id, lhp.ma_lhp, lhp.mon_hoc_id, mh.ma_mon, mh.ten_mon,
               lhp.hoc_ky_id, hk.ten_hoc_ky,
               lhp.giang_vien_id, gv.ho_ten AS ten_gv,
               lhp.ty_le_cc, lhp.ty_le_gk, lhp.ty_le_ck,
               lhp.trang_thai, lhp.cong_nhap_diem_mo,
               (SELECT COUNT(*) FROM ds_lhp ds WHERE ds.lhp_id = lhp.lhp_id) AS so_sv
        FROM lop_hoc_phan lhp
        JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
        JOIN hoc_ky hk ON hk.hoc_ky_id = lhp.hoc_ky_id
        LEFT JOIN giang_vien gv ON gv.giang_vien_id = lhp.giang_vien_id
        WHERE 1 = 1
    """
    params: List = []
    if hoc_ky_id:
        sql += " AND lhp.hoc_ky_id = %s"
        params.append(hoc_ky_id)
    if giang_vien_id:
        sql += " AND lhp.giang_vien_id = %s"
        params.append(giang_vien_id)
    sql += " ORDER BY hk.ten_hoc_ky DESC, lhp.ma_lhp ASC"

    with db_cursor() as (_, cursor):
        cursor.execute(sql, tuple(params))
        return cursor.fetchall()


def create_lhp(payload: Dict) -> Dict:
    ma_lhp = (payload.get("ma_lhp") or "").strip()
    mon_hoc_id = (payload.get("mon_hoc_id") or "").strip()
    hoc_ky_id = (payload.get("hoc_ky_id") or "").strip()
    giang_vien_id = (payload.get("giang_vien_id") or "").strip() or None
    ty_le_cc = float(payload.get("ty_le_cc", 10))
    ty_le_gk = float(payload.get("ty_le_gk", 30))
    ty_le_ck = float(payload.get("ty_le_ck", 60))

    if not ma_lhp or not mon_hoc_id or not hoc_ky_id:
        raise ValueError("Thieu du lieu tao LHP")
    if round(ty_le_cc + ty_le_gk + ty_le_ck, 2) != 100.0:
        raise ValueError("Tong trong so phai bang 100")

    lhp_id = _new_id()

    def _tx(cursor):
        cursor.execute(
            """
            INSERT INTO lop_hoc_phan (
                lhp_id, ma_lhp, mon_hoc_id, hoc_ky_id, giang_vien_id,
                ty_le_cc, ty_le_gk, ty_le_ck, trang_thai, cong_nhap_diem_mo
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, 'MO', FALSE)
            """,
            (lhp_id, ma_lhp, mon_hoc_id, hoc_ky_id, giang_vien_id, ty_le_cc, ty_le_gk, ty_le_ck),
        )

    execute_transaction(_tx)
    return {"lhp_id": lhp_id}


def update_lhp(lhp_id: str, payload: Dict) -> Dict:
    fields = []
    params: List = []
    for key in ("mon_hoc_id", "hoc_ky_id", "giang_vien_id", "ty_le_cc", "ty_le_gk", "ty_le_ck", "trang_thai"):
        if key in payload:
            fields.append(f"{key} = %s")
            params.append(payload[key])

    if {"ty_le_cc", "ty_le_gk", "ty_le_ck"}.issubset(payload.keys()):
        total = float(payload["ty_le_cc"]) + float(payload["ty_le_gk"]) + float(payload["ty_le_ck"])
        if round(total, 2) != 100.0:
            raise ValueError("Tong trong so phai bang 100")

    if not fields:
        return {"success": True}

    params.append(lhp_id)
    execute_transaction(lambda cursor: cursor.execute(f"UPDATE lop_hoc_phan SET {', '.join(fields)} WHERE lhp_id = %s", tuple(params)))
    return {"success": True}


def delete_lhp(lhp_id: str) -> Dict:
    # Keep historical consistency by closing class instead of hard delete.
    execute_transaction(lambda cursor: cursor.execute("UPDATE lop_hoc_phan SET trang_thai = 'DONG' WHERE lhp_id = %s", (lhp_id,)))
    return {"success": True}


def list_lhp_students(lhp_id: str) -> List[Dict]:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT ds.ds_lhp_id, sv.sinh_vien_id, sv.msv, sv.ho_ten,
                   lsh.ma_lop, lsh.ten_lop,
                   mh.ma_mon, mh.ten_mon,
                   ds.diem_cc, ds.diem_gk, ds.diem_ck, ds.diem_tong, ds.trang_thai_diem
            FROM ds_lhp ds
            JOIN sinh_vien sv ON sv.sinh_vien_id = ds.sinh_vien_id
            LEFT JOIN lop_sinh_hoat lsh ON lsh.lop_id = sv.lop_id
            JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
            JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
            WHERE ds.lhp_id = %s
            ORDER BY sv.msv ASC
            """,
            (lhp_id,),
        )
        return cursor.fetchall()


def add_student_to_lhp(lhp_id: str, sinh_vien_id: str) -> Dict:
    ds_lhp_id = _new_id()
    ls_id = _new_id()

    def _tx(cursor):
        cursor.execute(
            """
            SELECT trang_thai, msv, ho_ten
            FROM sinh_vien
            WHERE sinh_vien_id = %s
            LIMIT 1
            """,
            (sinh_vien_id,),
        )
        sv = cursor.fetchone()
        if sv is None:
            raise ValueError("Khong tim thay sinh vien")
        if sv["trang_thai"] == "BUOC_THOI_HOC":
            raise ValueError(
                f"Sinh vien {sv['msv']} - {sv['ho_ten']} da bi BUOC_THOI_HOC, khong duoc them vao LHP"
            )

        cursor.execute(
            """
            SELECT mon_hoc_id, hoc_ky_id
            FROM lop_hoc_phan
            WHERE lhp_id = %s
            LIMIT 1
            """,
            (lhp_id,),
        )
        lhp = cursor.fetchone()
        if lhp is None:
            raise ValueError("Khong tim thay LHP")

        cursor.execute(
            """
            SELECT COALESCE(MAX(lan_hoc), 0) + 1 AS lan_hoc
            FROM lich_su_hoc_mon
            WHERE sinh_vien_id = %s AND mon_hoc_id = %s
            """,
            (sinh_vien_id, lhp["mon_hoc_id"]),
        )
        lan_hoc = int((cursor.fetchone() or {"lan_hoc": 1})["lan_hoc"])

        cursor.execute(
            """
            INSERT INTO ds_lhp (ds_lhp_id, lhp_id, sinh_vien_id, trang_thai_diem)
            VALUES (%s, %s, %s, 'CHUA_NHAP')
            """,
            (ds_lhp_id, lhp_id, sinh_vien_id),
        )

        cursor.execute(
            """
            INSERT INTO lich_su_hoc_mon (ls_id, sinh_vien_id, mon_hoc_id, lhp_id, hoc_ky_id, lan_hoc)
            VALUES (%s, %s, %s, %s, %s, %s)
            """,
            (ls_id, sinh_vien_id, lhp["mon_hoc_id"], lhp_id, lhp["hoc_ky_id"], lan_hoc),
        )

    execute_transaction(_tx)
    return {"success": True, "ds_lhp_id": ds_lhp_id}


def remove_student_from_lhp(lhp_id: str, sinh_vien_id: str) -> Dict:
    def _tx(cursor):
        cursor.execute(
            """
            SELECT ds_lhp_id
            FROM ds_lhp
            WHERE lhp_id = %s AND sinh_vien_id = %s
            LIMIT 1
            """,
            (lhp_id, sinh_vien_id),
        )
        row = cursor.fetchone()
        if row is None:
            raise ValueError("Khong tim thay sinh vien trong LHP")

        ds_lhp_id = row["ds_lhp_id"]

        cursor.execute(
            "DELETE FROM audit_diem WHERE ds_lhp_id = %s",
            (ds_lhp_id,),
        )
        cursor.execute(
            "DELETE FROM yeu_cau_sua_diem WHERE ds_lhp_id = %s",
            (ds_lhp_id,),
        )
        cursor.execute(
            "DELETE FROM lich_su_hoc_mon WHERE lhp_id = %s AND sinh_vien_id = %s",
            (lhp_id, sinh_vien_id),
        )
        cursor.execute(
            "DELETE FROM ds_lhp WHERE lhp_id = %s AND sinh_vien_id = %s",
            (lhp_id, sinh_vien_id),
        )

    execute_transaction(_tx)
    return {"success": True}


def mo_khoa_nhap_diem(lhp_id: str, open_gate: bool) -> Dict:
    if open_gate:
        sql = "UPDATE lop_hoc_phan SET cong_nhap_diem_mo = TRUE, trang_thai = 'DANG_NHAP' WHERE lhp_id = %s"
    else:
        sql = "UPDATE lop_hoc_phan SET cong_nhap_diem_mo = FALSE WHERE lhp_id = %s"

    execute_transaction(lambda cursor: cursor.execute(sql, (lhp_id,)))
    return {"success": True}
