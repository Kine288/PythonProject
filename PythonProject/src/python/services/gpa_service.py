from __future__ import annotations

from decimal import Decimal, ROUND_HALF_UP
from typing import Dict, List, Tuple
import uuid

from core.database import db_cursor, execute_transaction


def _new_id() -> str:
    return uuid.uuid4().hex


def _round2(value: Decimal) -> Decimal:
    return value.quantize(Decimal("0.01"), rounding=ROUND_HALF_UP)


def _is_expelled_student(sinh_vien_id: str) -> bool:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT trang_thai
            FROM sinh_vien
            WHERE sinh_vien_id = %s
            LIMIT 1
            """,
            (sinh_vien_id,),
        )
        row = cursor.fetchone()
        return bool(row and row.get("trang_thai") == "BUOC_THOI_HOC")


def _weighted_gpa(rows: List[Dict]) -> Tuple[Decimal, Decimal, int]:
    total_credit = 0
    sum_he10 = Decimal("0")
    sum_he4 = Decimal("0")

    for row in rows:
        diem_tong = row.get("diem_tong")
        so_tin_chi = row.get("so_tin_chi")
        if diem_tong is None or so_tin_chi is None:
            continue

        tc = int(so_tin_chi)
        if tc <= 0:
            continue

        d10 = Decimal(str(diem_tong))
        _, d4 = br2_quy_doi(d10)

        sum_he10 += d10 * Decimal(tc)
        sum_he4 += d4 * Decimal(tc)
        total_credit += tc

    if total_credit == 0:
        return Decimal("0.00"), Decimal("0.00"), 0

    gpa10 = _round2(sum_he10 / Decimal(total_credit))
    gpa4 = _round2(sum_he4 / Decimal(total_credit))
    return gpa10, gpa4, total_credit


def br2_quy_doi(diem_he_10) -> Tuple[str, Decimal]:
    d = Decimal(str(diem_he_10))
    if d >= Decimal("8.5"):
        return "A", Decimal("4.0")
    if d >= Decimal("7.8"):
        return "B+", Decimal("3.5")
    if d >= Decimal("7.0"):
        return "B", Decimal("3.0")
    if d >= Decimal("6.3"):
        return "C+", Decimal("2.5")
    if d >= Decimal("5.5"):
        return "C", Decimal("2.0")
    if d >= Decimal("4.8"):
        return "D+", Decimal("1.5")
    if d >= Decimal("4.0"):
        return "D", Decimal("1.0")
    return "F", Decimal("0.0")


def co_mon_f(sinh_vien_id: str) -> bool:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT 1
            FROM ds_lhp ds
            JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
            JOIN lich_su_hoc_mon ls ON ls.lhp_id = lhp.lhp_id AND ls.sinh_vien_id = ds.sinh_vien_id
            WHERE ds.sinh_vien_id = %s
              AND ds.diem_tong < 4.0
            LIMIT 1
            """,
            (sinh_vien_id,),
        )
        return cursor.fetchone() is not None


def dem_hoc_ky_da_qua(sinh_vien_id: str) -> int:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT COUNT(*) AS total
            FROM ket_qua_hoc_ky
            WHERE sinh_vien_id = %s
            """,
            (sinh_vien_id,),
        )
        row = cursor.fetchone() or {"total": 0}
        return int(row["total"] or 0)


def br3_tinh_gpa_hoc_ky(sinh_vien_id: str, hoc_ky_id: str) -> Tuple[Decimal, Decimal, int]:
    if _is_expelled_student(sinh_vien_id):
        return Decimal("0.00"), Decimal("0.00"), 0

    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT ds.diem_tong, mh.so_tin_chi
            FROM ds_lhp ds
            JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
            JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
            JOIN sinh_vien sv ON sv.sinh_vien_id = ds.sinh_vien_id
            WHERE ds.sinh_vien_id = %s
              AND lhp.hoc_ky_id = %s
              AND lhp.trang_thai = 'DA_DUYET'
              AND sv.trang_thai <> 'BUOC_THOI_HOC'
              AND mh.tinh_gpa = TRUE
              AND ds.diem_tong IS NOT NULL
            """,
            (sinh_vien_id, hoc_ky_id),
        )
        rows = cursor.fetchall()

    return _weighted_gpa(rows)


def br3_tinh_gpa_tich_luy(sinh_vien_id: str) -> Tuple[Decimal, Decimal, int]:
    if _is_expelled_student(sinh_vien_id):
        return Decimal("0.00"), Decimal("0.00"), 0

    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT ds.diem_tong, mh.so_tin_chi
            FROM lich_su_hoc_mon ls
            JOIN (
                SELECT mon_hoc_id, MAX(lan_hoc) AS lan_hoc_cuoi
                FROM lich_su_hoc_mon
                WHERE sinh_vien_id = %s
                GROUP BY mon_hoc_id
            ) x ON x.mon_hoc_id = ls.mon_hoc_id AND x.lan_hoc_cuoi = ls.lan_hoc
            JOIN ds_lhp ds ON ds.lhp_id = ls.lhp_id AND ds.sinh_vien_id = ls.sinh_vien_id
            JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
            JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
                        JOIN sinh_vien sv ON sv.sinh_vien_id = ls.sinh_vien_id
            WHERE ls.sinh_vien_id = %s
              AND lhp.trang_thai = 'DA_DUYET'
                            AND sv.trang_thai <> 'BUOC_THOI_HOC'
              AND mh.tinh_gpa = TRUE
              AND ds.diem_tong IS NOT NULL
            """,
            (sinh_vien_id, sinh_vien_id),
        )
        rows = cursor.fetchall()

    return _weighted_gpa(rows)


def br4_xep_loai(gpa_tich_luy_he4: Decimal, sinh_vien_id: str) -> str:
    g = Decimal(str(gpa_tich_luy_he4))
    if g >= Decimal("3.60"):
        xep_loai = "Xuat sac"
    elif g >= Decimal("3.20"):
        xep_loai = "Gioi"
    elif g >= Decimal("2.50"):
        xep_loai = "Kha"
    elif g >= Decimal("2.00"):
        xep_loai = "Trung binh"
    elif g >= Decimal("1.00"):
        xep_loai = "Yeu"
    else:
        xep_loai = "Kem"

    if xep_loai == "Xuat sac" and co_mon_f(sinh_vien_id):
        return "Gioi"
    if xep_loai == "Gioi" and co_mon_f(sinh_vien_id):
        return "Kha"
    return xep_loai


def br5_xac_dinh_canh_bao(sinh_vien_id: str, hoc_ky_id: str, gpa_tich_luy_he4: Decimal) -> int:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT COUNT(*) AS total,
                   SUM(CASE WHEN hoc_ky_id = %s THEN 1 ELSE 0 END) AS has_current
            FROM ket_qua_hoc_ky
            WHERE sinh_vien_id = %s
            """,
            (hoc_ky_id, sinh_vien_id),
        )
        count_row = cursor.fetchone() or {"total": 0, "has_current": 0}

    so_hk_da_qua = int(count_row["total"] or 0)
    if int(count_row["has_current"] or 0) == 0:
        so_hk_da_qua += 1

    if so_hk_da_qua <= 0:
        so_hk_da_qua = 1

    if so_hk_da_qua == 1:
        nguong = Decimal("1.20")
    elif so_hk_da_qua == 2:
        nguong = Decimal("1.40")
    else:
        nguong = Decimal("1.60")

    if Decimal(str(gpa_tich_luy_he4)) >= nguong:
        return 0

    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT muc_canh_bao
            FROM ket_qua_hoc_ky
            WHERE sinh_vien_id = %s AND hoc_ky_id <> %s
            ORDER BY hoc_ky_id DESC
            """,
            (sinh_vien_id, hoc_ky_id),
        )
        history = [int(r["muc_canh_bao"] or 0) for r in cursor.fetchall()]

    prev = history[0] if history else 0
    current = 2 if prev >= 1 else 1
    total_warn = sum(1 for level in history if level > 0) + 1

    if (current == 2 and prev == 2) or total_warn >= 3:
        return 3
    return current


def _module_tu_dong_canh_bao_hoc_vu(hoc_ky_id: str, sinh_vien_ids: List[str]) -> List[Dict]:
    results: List[Dict] = []

    def _tx(cursor):
        for sv_id in sinh_vien_ids:
            gpa_hk_10, gpa_hk_4, _ = br3_tinh_gpa_hoc_ky(sv_id, hoc_ky_id)
            gpa_tl_10, gpa_tl_4, tong_tin_chi = br3_tinh_gpa_tich_luy(sv_id)
            xep_loai = br4_xep_loai(gpa_tl_4, sv_id)
            canh_bao = br5_xac_dinh_canh_bao(sv_id, hoc_ky_id, gpa_tl_4)

            cursor.execute(
                """
                INSERT INTO ket_qua_hoc_ky (
                    kqhk_id, sinh_vien_id, hoc_ky_id,
                    gpa_hk_he10, gpa_hk_he4,
                    gpa_tich_luy_he10, gpa_tich_luy_he4,
                    tong_tin_chi_dat, xep_loai, muc_canh_bao
                )
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                ON DUPLICATE KEY UPDATE
                    gpa_hk_he10 = VALUES(gpa_hk_he10),
                    gpa_hk_he4 = VALUES(gpa_hk_he4),
                    gpa_tich_luy_he10 = VALUES(gpa_tich_luy_he10),
                    gpa_tich_luy_he4 = VALUES(gpa_tich_luy_he4),
                    tong_tin_chi_dat = VALUES(tong_tin_chi_dat),
                    xep_loai = VALUES(xep_loai),
                    muc_canh_bao = VALUES(muc_canh_bao)
                """,
                (
                    _new_id(),
                    sv_id,
                    hoc_ky_id,
                    float(gpa_hk_10),
                    float(gpa_hk_4),
                    float(gpa_tl_10),
                    float(gpa_tl_4),
                    tong_tin_chi,
                    xep_loai,
                    canh_bao,
                ),
            )

            if canh_bao == 3:
                cursor.execute(
                    "UPDATE sinh_vien SET trang_thai = 'BUOC_THOI_HOC' WHERE sinh_vien_id = %s",
                    (sv_id,),
                )

            results.append(
                {
                    "sinh_vien_id": sv_id,
                    "gpa_hk_he10": float(gpa_hk_10),
                    "gpa_hk_he4": float(gpa_hk_4),
                    "gpa_tich_luy_he10": float(gpa_tl_10),
                    "gpa_tich_luy_he4": float(gpa_tl_4),
                    "xep_loai": xep_loai,
                    "muc_canh_bao": canh_bao,
                }
            )

    execute_transaction(_tx)
    return results


def tinh_va_luu_gpa_hoc_ky(hoc_ky_id: str) -> List[Dict]:
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT DISTINCT ds.sinh_vien_id
            FROM ds_lhp ds
            JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
            JOIN sinh_vien sv ON sv.sinh_vien_id = ds.sinh_vien_id
            WHERE lhp.hoc_ky_id = %s
              AND sv.trang_thai <> 'BUOC_THOI_HOC'
            """,
            (hoc_ky_id,),
        )
        sinh_vien_ids = [r["sinh_vien_id"] for r in cursor.fetchall()]
    return _module_tu_dong_canh_bao_hoc_vu(hoc_ky_id, sinh_vien_ids)


def lay_ket_qua_sinh_vien(sinh_vien_id: str, hoc_ky_id: str | None = None):
    with db_cursor() as (_, cursor):
        sql = """
            SELECT kq.*, sv.msv, sv.ho_ten
            FROM ket_qua_hoc_ky kq
            JOIN sinh_vien sv ON sv.sinh_vien_id = kq.sinh_vien_id
            WHERE kq.sinh_vien_id = %s
        """
        params = [sinh_vien_id]
        if hoc_ky_id:
            sql += " AND kq.hoc_ky_id = %s"
            params.append(hoc_ky_id)
        sql += " ORDER BY kq.hoc_ky_id DESC"
        cursor.execute(sql, tuple(params))
        return cursor.fetchall()


def lay_danh_sach_canh_bao(hoc_ky_id: str):
    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT kq.sinh_vien_id, sv.msv, sv.ho_ten,
                   kq.gpa_tich_luy_he4, kq.xep_loai, kq.muc_canh_bao
            FROM ket_qua_hoc_ky kq
            JOIN sinh_vien sv ON sv.sinh_vien_id = kq.sinh_vien_id
            WHERE kq.hoc_ky_id = %s AND kq.muc_canh_bao > 0
            ORDER BY kq.muc_canh_bao DESC, kq.gpa_tich_luy_he4 ASC
            """,
            (hoc_ky_id,),
        )
        return cursor.fetchall()
