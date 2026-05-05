from flask import Blueprint, jsonify, request

from config.db_config import get_database_connection
from services.canh_bao import xac_dinh_muc_canh_bao
from services.tinh_gpa import (
    luu_ket_qua_hoc_ky,
    tinh_gpa_hoc_ky,
    tinh_gpa_tich_luy,
)
from services.xep_loai import xep_loai_hoc_luc


gpa_bp = Blueprint("gpa_api", __name__, url_prefix="/api/gpa")


def _error(message: str, status: int = 400):
    return jsonify({"success": False, "message": message}), status


def _ok(data=None, message: str = ""):
    return jsonify({"success": True, "message": message, "data": data})


def _get_targets(hoc_ky_id: str, sinh_vien_id: str = ""):
    conn = get_database_connection()
    if not conn:
        raise ConnectionError("Khong ket noi duoc database")

    try:
        with conn.cursor() as cursor:
            if sinh_vien_id:
                cursor.execute(
                    """
                    SELECT DISTINCT ds.sinh_vien_id
                    FROM ds_lhp ds
                    JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
                    WHERE lhp.hoc_ky_id = %s AND ds.sinh_vien_id = %s
                    """,
                    (hoc_ky_id, sinh_vien_id),
                )
            else:
                cursor.execute(
                    """
                    SELECT DISTINCT ds.sinh_vien_id
                    FROM ds_lhp ds
                    JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
                    WHERE lhp.hoc_ky_id = %s
                    """,
                    (hoc_ky_id,),
                )
            rows = cursor.fetchall()
            return [row["sinh_vien_id"] for row in rows]
    finally:
        conn.close()


@gpa_bp.post("/recalculate")
def recalculate_gpa():
    payload = request.get_json(silent=True) or {}
    hoc_ky_id = (payload.get("hoc_ky_id") or "").strip()
    sinh_vien_id = (payload.get("sinh_vien_id") or "").strip()

    if not hoc_ky_id:
        return _error("Thieu hoc_ky_id")

    try:
        targets = _get_targets(hoc_ky_id, sinh_vien_id)
        if not targets:
            return _ok([], "Khong co sinh vien nao can tinh")

        results = []
        for sv_id in targets:
            gpa_hk_10, gpa_hk_4, _ = tinh_gpa_hoc_ky(sv_id, hoc_ky_id)
            gpa_tl_10, gpa_tl_4, tong_tin_chi = tinh_gpa_tich_luy(sv_id)
            xep_loai = xep_loai_hoc_luc(gpa_tl_4, sv_id)
            muc_canh_bao = xac_dinh_muc_canh_bao(sv_id, gpa_tl_4)

            luu_ket_qua_hoc_ky(
                sv_id,
                hoc_ky_id,
                gpa_hk_10,
                gpa_hk_4,
                gpa_tl_10,
                gpa_tl_4,
                tong_tin_chi,
                xep_loai,
                muc_canh_bao,
            )

            results.append(
                {
                    "sinh_vien_id": sv_id,
                    "gpa_he_10": gpa_hk_10,
                    "gpa_he_4": gpa_hk_4,
                    "gpa_tich_luy_he_10": gpa_tl_10,
                    "gpa_tich_luy_he_4": gpa_tl_4,
                    "tong_tin_chi": tong_tin_chi,
                    "xep_loai": xep_loai,
                    "muc_canh_bao": muc_canh_bao,
                }
            )

        return _ok(results, "Da tinh va luu ket qua hoc ky")
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(f"Khong the tinh GPA: {exc}", 500)


@gpa_bp.get("/students/<sinh_vien_id>")
def get_student_result(sinh_vien_id: str):
    hoc_ky_id = (request.args.get("hoc_ky_id") or "").strip()
    if not hoc_ky_id:
        return _error("Thieu hoc_ky_id")

    conn = get_database_connection()
    if not conn:
        return _error("Khong ket noi duoc database", 500)

    try:
        with conn.cursor() as cursor:
            cursor.execute(
                """
                SELECT kq.*, sv.msv, sv.ten_sv
                FROM ket_qua_hoc_ky kq
                JOIN sinh_vien sv ON sv.sinh_vien_id = kq.sinh_vien_id
                WHERE kq.sinh_vien_id = %s AND kq.hoc_ky_id = %s
                LIMIT 1
                """,
                (sinh_vien_id, hoc_ky_id),
            )
            row = cursor.fetchone()
            if not row:
                return _error("Khong tim thay ket qua", 404)
            return _ok(row)
    finally:
        conn.close()


@gpa_bp.get("/warnings")
def list_warnings():
    hoc_ky_id = (request.args.get("hoc_ky_id") or "").strip()
    if not hoc_ky_id:
        return _error("Thieu hoc_ky_id")

    conn = get_database_connection()
    if not conn:
        return _error("Khong ket noi duoc database", 500)

    try:
        with conn.cursor() as cursor:
            cursor.execute(
                """
                SELECT kq.sinh_vien_id, sv.msv, sv.ten_sv,
                       kq.gpa_tich_luy_he_4, kq.muc_canh_bao, kq.xep_loai
                FROM ket_qua_hoc_ky kq
                JOIN sinh_vien sv ON sv.sinh_vien_id = kq.sinh_vien_id
                WHERE kq.hoc_ky_id = %s AND kq.muc_canh_bao > 0
                ORDER BY kq.gpa_tich_luy_he_4 ASC, sv.msv ASC
                """,
                (hoc_ky_id,),
            )
            rows = cursor.fetchall()
            return _ok(rows)
    finally:
        conn.close()