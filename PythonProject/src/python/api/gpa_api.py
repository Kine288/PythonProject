from flask import Blueprint, jsonify, request

from services.gpa_service import (
    lay_danh_sach_canh_bao,
    lay_ket_qua_sinh_vien,
    tinh_va_luu_gpa_hoc_ky,
)


gpa_bp = Blueprint("gpa", __name__, url_prefix="/api/gpa")


@gpa_bp.post("/tinh-hoc-ky/<hk_id>")
def post_tinh_hoc_ky(hk_id: str):
    try:
        data = tinh_va_luu_gpa_hoc_ky(hk_id)
        return jsonify({"success": True, "data": data}), 200
    except ValueError as exc:
        return jsonify({"success": False, "message": str(exc)}), 400
    except Exception as exc:
        return jsonify({"success": False, "message": str(exc)}), 500


@gpa_bp.get("/ket-qua/<sv_id>")
def get_ket_qua_sv(sv_id: str):
    hoc_ky_id = (request.args.get("hoc_ky_id") or "").strip() or None
    try:
        rows = lay_ket_qua_sinh_vien(sv_id, hoc_ky_id)
        return jsonify({"success": True, "data": rows}), 200
    except Exception as exc:
        return jsonify({"success": False, "message": str(exc)}), 500


@gpa_bp.get("/canh-bao/<hk_id>")
def get_canh_bao_hk(hk_id: str):
    try:
        rows = lay_danh_sach_canh_bao(hk_id)
        return jsonify({"success": True, "data": rows}), 200
    except Exception as exc:
        return jsonify({"success": False, "message": str(exc)}), 500