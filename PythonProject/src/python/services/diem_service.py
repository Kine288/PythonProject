from __future__ import annotations

from decimal import Decimal, ROUND_HALF_UP
import json
import uuid
from typing import Dict, List

from core.database import db_cursor, execute_transaction


def _new_id() -> str:
    return uuid.uuid4().hex


def _to_decimal(value) -> Decimal:
    if value is None:
        return Decimal("0")
    return Decimal(str(value))


def br1_tinh_diem_tong(diem_cc, diem_gk, diem_ck, ty_le_cc, ty_le_gk, ty_le_ck) -> Decimal:
    tong = (
        _to_decimal(diem_cc) * _to_decimal(ty_le_cc) / Decimal("100")
        + _to_decimal(diem_gk) * _to_decimal(ty_le_gk) / Decimal("100")
        + _to_decimal(diem_ck) * _to_decimal(ty_le_ck) / Decimal("100")
    )
    return tong.quantize(Decimal("0.1"), rounding=ROUND_HALF_UP)


def _validate_ty_le_100(ty_le_cc, ty_le_gk, ty_le_ck) -> None:
    total = _to_decimal(ty_le_cc) + _to_decimal(ty_le_gk) + _to_decimal(ty_le_ck)
    if total != Decimal("100"):
        raise ValueError(f"Tong trong so khong hop le: {total}")


def lay_bang_diem_lhp(lhp_id: str) -> List[Dict]:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT
                ds.ds_lhp_id,
                ds.sinh_vien_id,
                sv.msv,
                sv.ho_ten,
                ds.diem_cc,
                ds.diem_gk,
                ds.diem_ck,
                ds.diem_tong,
                ds.trang_thai_diem,
                lhp.trang_thai AS trang_thai_lhp,
                lhp.cong_nhap_diem_mo
            FROM ds_lhp ds
            JOIN sinh_vien sv ON sv.sinh_vien_id = ds.sinh_vien_id
            JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
            WHERE ds.lhp_id = %s
            ORDER BY sv.msv ASC
            """,
            (lhp_id,),
        )
        return cursor.fetchall()


def luu_nhap_diem_lhp(lhp_id: str, tai_khoan_id: str, rows: List[Dict], ly_do: str = "") -> Dict:
    if not rows:
        raise ValueError("Danh sach diem rong")

    def _tx(cursor):
        cursor.execute("SELECT lhp_id FROM lop_hoc_phan WHERE lhp_id = %s LIMIT 1", (lhp_id,))
        if cursor.fetchone() is None:
            raise ValueError("Khong tim thay LHP")

        updated = 0
        for item in rows:
            ds_lhp_id = item.get("ds_lhp_id")
            if not ds_lhp_id:
                raise ValueError("Thieu ds_lhp_id")

            diem_cc = item.get("diem_cc")
            diem_gk = item.get("diem_gk")
            diem_ck = item.get("diem_ck")

            cursor.execute(
                """
                SELECT diem_cc, diem_gk, diem_ck
                FROM ds_lhp
                WHERE ds_lhp_id = %s AND lhp_id = %s
                LIMIT 1
                """,
                (ds_lhp_id, lhp_id),
            )
            before = cursor.fetchone()
            if before is None:
                raise ValueError(f"Khong tim thay ds_lhp_id: {ds_lhp_id}")

            cursor.execute(
                """
                UPDATE ds_lhp
                SET diem_cc = %s,
                    diem_gk = %s,
                    diem_ck = %s,
                    trang_thai_diem = 'NHAP_NHAP'
                WHERE ds_lhp_id = %s
                """,
                (diem_cc, diem_gk, diem_ck, ds_lhp_id),
            )

            after = {"diem_cc": diem_cc, "diem_gk": diem_gk, "diem_ck": diem_ck}
            cursor.execute(
                """
                INSERT INTO audit_diem
                    (audit_id, ds_lhp_id, tai_khoan_id, truoc_thay_doi, sau_thay_doi, ly_do)
                VALUES (%s, %s, %s, %s, %s, %s)
                """,
                (
                    _new_id(),
                    ds_lhp_id,
                    tai_khoan_id,
                    json.dumps({"diem_cc": before["diem_cc"], "diem_gk": before["diem_gk"], "diem_ck": before["diem_ck"]}),
                    json.dumps(after),
                    ly_do or "Luu nhap diem",
                ),
            )
            updated += 1

        return {"updated": updated}

    result = execute_transaction(_tx)
    return {"success": True, **result}


def gui_duyet_lhp(lhp_id: str) -> Dict:
    def _tx(cursor):
        cursor.execute(
            "UPDATE lop_hoc_phan SET trang_thai = 'CHO_DUYET' WHERE lhp_id = %s",
            (lhp_id,),
        )
        if cursor.rowcount == 0:
            raise ValueError("Khong tim thay LHP")

    execute_transaction(_tx)
    return {"success": True, "message": "Da gui duyet"}


def tu_choi_lhp(lhp_id: str) -> Dict:
    def _tx(cursor):
        cursor.execute(
            "UPDATE lop_hoc_phan SET trang_thai = 'DANG_NHAP' WHERE lhp_id = %s",
            (lhp_id,),
        )
        if cursor.rowcount == 0:
            raise ValueError("Khong tim thay LHP")

    execute_transaction(_tx)
    return {"success": True, "message": "Da tu choi duyet"}


def duyet_lhp_va_tinh_diem(lhp_id: str, tai_khoan_id: str) -> Dict:
    def _tx(cursor):
        cursor.execute(
            """
            SELECT ty_le_cc, ty_le_gk, ty_le_ck
            FROM lop_hoc_phan
            WHERE lhp_id = %s
            LIMIT 1
            """,
            (lhp_id,),
        )
        lhp = cursor.fetchone()
        if lhp is None:
            raise ValueError("Khong tim thay LHP")

        _validate_ty_le_100(lhp["ty_le_cc"], lhp["ty_le_gk"], lhp["ty_le_ck"])

        cursor.execute(
            """
            SELECT ds_lhp_id, diem_cc, diem_gk, diem_ck, diem_tong
            FROM ds_lhp
            WHERE lhp_id = %s
            """,
            (lhp_id,),
        )
        rows = cursor.fetchall()
        if not rows:
            raise ValueError("LHP chua co danh sach sinh vien")

        count = 0
        for row in rows:
            if row["diem_cc"] is None or row["diem_gk"] is None or row["diem_ck"] is None:
                raise ValueError("Con sinh vien chua nhap du diem thanh phan")

            diem_tong = br1_tinh_diem_tong(
                row["diem_cc"],
                row["diem_gk"],
                row["diem_ck"],
                lhp["ty_le_cc"],
                lhp["ty_le_gk"],
                lhp["ty_le_ck"],
            )

            cursor.execute(
                """
                UPDATE ds_lhp
                SET diem_tong = %s,
                    trang_thai_diem = 'DA_DUYET'
                WHERE ds_lhp_id = %s
                """,
                (float(diem_tong), row["ds_lhp_id"]),
            )

            cursor.execute(
                """
                INSERT INTO audit_diem
                    (audit_id, ds_lhp_id, tai_khoan_id, truoc_thay_doi, sau_thay_doi, ly_do)
                VALUES (%s, %s, %s, %s, %s, %s)
                """,
                (
                    _new_id(),
                    row["ds_lhp_id"],
                    tai_khoan_id,
                    json.dumps({"diem_tong": row["diem_tong"]}),
                    json.dumps({"diem_tong": float(diem_tong)}),
                    "Duyet diem LHP",
                ),
            )
            count += 1

        cursor.execute(
            """
            UPDATE lop_hoc_phan
            SET trang_thai = 'DA_DUYET',
                cong_nhap_diem_mo = FALSE
            WHERE lhp_id = %s
            """,
            (lhp_id,),
        )

        return {"so_luong": count}

    result = execute_transaction(_tx)
    return {"success": True, **result}


def tao_yeu_cau_sua_diem(ds_lhp_id: str, giang_vien_id: str, ly_do: str) -> Dict:
    if not ly_do:
        raise ValueError("Ly do khong duoc de trong")

    def _tx(cursor):
        cursor.execute(
            """
            INSERT INTO yeu_cau_sua_diem
                (yc_id, ds_lhp_id, giang_vien_id, ly_do, trang_thai)
            VALUES (%s, %s, %s, %s, 'CHO_XU_LY')
            """,
            (_new_id(), ds_lhp_id, giang_vien_id, ly_do),
        )

    execute_transaction(_tx)
    return {"success": True}


def danh_sach_yeu_cau_sua() -> List[Dict]:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT yc.yc_id, yc.ds_lhp_id, yc.giang_vien_id, yc.ly_do,
                   yc.trang_thai, yc.giao_vu_xu_ly, yc.ghi_chu_giao_vu, yc.ngay_tao
            FROM yeu_cau_sua_diem yc
            ORDER BY yc.ngay_tao DESC
            """
        )
        return cursor.fetchall()


def xu_ly_yeu_cau_sua(yc_id: str, giao_vu_id: str, chap_thuan: bool, ghi_chu: str = "") -> Dict:
    trang_thai = "CHAP_THUAN" if chap_thuan else "TU_CHOI"

    def _tx(cursor):
        cursor.execute(
            """
            UPDATE yeu_cau_sua_diem
            SET trang_thai = %s,
                giao_vu_xu_ly = %s,
                ghi_chu_giao_vu = %s
            WHERE yc_id = %s
            """,
            (trang_thai, giao_vu_id, ghi_chu, yc_id),
        )
        if cursor.rowcount == 0:
            raise ValueError("Khong tim thay yeu cau")

    execute_transaction(_tx)
    return {"success": True, "trang_thai": trang_thai}
