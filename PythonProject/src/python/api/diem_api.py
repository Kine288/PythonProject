from flask import Blueprint, jsonify, request

from services.diem_service import (
    danh_sach_yeu_cau_sua,
    duyet_lhp_va_tinh_diem,
    gui_duyet_lhp,
    lay_bang_diem_lhp,
    luu_nhap_diem_lhp,
    tao_yeu_cau_sua_diem,
    tu_choi_lhp,
    xu_ly_yeu_cau_sua,
)


diem_bp = Blueprint("diem", __name__, url_prefix="/api/diem")


@diem_bp.get("/lhp/<lhp_id>")
def get_diem_lhp(lhp_id: str):
    try:
        return jsonify({"success": True, "data": lay_bang_diem_lhp(lhp_id)})
    except Exception as exc:
        return jsonify({"success": False, "message": str(exc)}), 500


@diem_bp.put("/lhp/<lhp_id>")
def put_diem_lhp(lhp_id: str):
    data = request.get_json(silent=True) or {}
    tai_khoan_id = (data.get("tai_khoan_id") or "").strip()
    rows = data.get("rows") or []
    ly_do = (data.get("ly_do") or "").strip()

    try:
        result = luu_nhap_diem_lhp(lhp_id, tai_khoan_id, rows, ly_do)
        return jsonify(result), 200
    except ValueError as exc:
        return jsonify({"success": False, "message": str(exc)}), 400
    except Exception as exc:
        return jsonify({"success": False, "message": str(exc)}), 500


@diem_bp.post("/lhp/<lhp_id>/gui-duyet")
def post_gui_duyet(lhp_id: str):
    try:
        return jsonify(gui_duyet_lhp(lhp_id)), 200
    except ValueError as exc:
        return jsonify({"success": False, "message": str(exc)}), 400
    except Exception as exc:
        return jsonify({"success": False, "message": str(exc)}), 500


@diem_bp.post("/lhp/<lhp_id>/duyet")
def post_duyet(lhp_id: str):
    data = request.get_json(silent=True) or {}
    tai_khoan_id = (data.get("tai_khoan_id") or "").strip()
    try:
        return jsonify(duyet_lhp_va_tinh_diem(lhp_id, tai_khoan_id)), 200
    except ValueError as exc:
        return jsonify({"success": False, "message": str(exc)}), 400
    except Exception as exc:
        return jsonify({"success": False, "message": str(exc)}), 500


@diem_bp.post("/lhp/<lhp_id>/tu-choi")
def post_tu_choi(lhp_id: str):
    try:
        return jsonify(tu_choi_lhp(lhp_id)), 200
    except ValueError as exc:
        return jsonify({"success": False, "message": str(exc)}), 400
    except Exception as exc:
        return jsonify({"success": False, "message": str(exc)}), 500


@diem_bp.post("/yeu-cau-sua")
def post_yeu_cau_sua():
    data = request.get_json(silent=True) or {}
    ds_lhp_id = (data.get("ds_lhp_id") or "").strip()
    giang_vien_id = (data.get("giang_vien_id") or "").strip()
    ly_do = (data.get("ly_do") or "").strip()

    try:
        return jsonify(tao_yeu_cau_sua_diem(ds_lhp_id, giang_vien_id, ly_do)), 200
    except ValueError as exc:
        return jsonify({"success": False, "message": str(exc)}), 400
    except Exception as exc:
        return jsonify({"success": False, "message": str(exc)}), 500


@diem_bp.get("/yeu-cau-sua")
def get_yeu_cau_sua():
    try:
        return jsonify({"success": True, "data": danh_sach_yeu_cau_sua()}), 200
    except Exception as exc:
        return jsonify({"success": False, "message": str(exc)}), 500


@diem_bp.put("/yeu-cau-sua/<yc_id>")
def put_yeu_cau_sua(yc_id: str):
    data = request.get_json(silent=True) or {}
    giao_vu_id = (data.get("giao_vu_id") or "").strip()
    chap_thuan = bool(data.get("chap_thuan"))
    ghi_chu = (data.get("ghi_chu") or "").strip()

    try:
        return jsonify(xu_ly_yeu_cau_sua(yc_id, giao_vu_id, chap_thuan, ghi_chu)), 200
    except ValueError as exc:
        return jsonify({"success": False, "message": str(exc)}), 400
    except Exception as exc:
        return jsonify({"success": False, "message": str(exc)}), 500