from __future__ import annotations

from flask import Blueprint, jsonify, request

from services.academic_service import (
    add_student_to_lhp,
    bang_diem_sinh_vien,
    create_lhp,
    create_sinh_vien_admin,
    delete_sinh_vien,
    delete_lhp,
    doi_vai_tro,
    get_sinh_vien,
    khoa_tai_khoan,
    list_lhp,
    list_lhp_students,
    list_sinh_vien,
    list_tai_khoan,
    mo_khoa_nhap_diem,
    remove_student_from_lhp,
    reset_mat_khau,
    update_lhp,
    update_sinh_vien_hoc_vu,
)


sinh_vien_bp = Blueprint("hoc_vu", __name__, url_prefix="/api")


def _ok(data=None, status: int = 200):
    return jsonify({"success": True, "data": data}), status


def _error(message: str, status: int = 400):
    return jsonify({"success": False, "message": message}), status


@sinh_vien_bp.get("/sinh-vien")
def api_list_sinh_vien():
    try:
        keyword = (request.args.get("search") or "").strip()
        lop_id = (request.args.get("lop_id") or "").strip()
        trang_thai = (request.args.get("trang_thai") or "").strip()
        return _ok(list_sinh_vien(keyword, lop_id, trang_thai))
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.post("/sinh-vien")
def api_create_sinh_vien():
    payload = request.get_json(silent=True) or {}
    try:
        data = create_sinh_vien_admin(payload)
        return _ok(data, 201)
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.get("/sinh-vien/<sv_id>")
def api_get_sinh_vien(sv_id: str):
    try:
        data = get_sinh_vien(sv_id)
        if data is None:
            return _error("Khong tim thay sinh vien", 404)
        return _ok(data)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.put("/sinh-vien/<sv_id>")
def api_update_sinh_vien(sv_id: str):
    payload = request.get_json(silent=True) or {}
    try:
        data = update_sinh_vien_hoc_vu(sv_id, payload)
        return _ok(data)
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.delete("/sinh-vien/<sv_id>")
def api_delete_sinh_vien(sv_id: str):
    payload = request.get_json(silent=True) or {}
    nguoi_thay_doi = (payload.get("nguoi_thay_doi") or "").strip()
    try:
        data = delete_sinh_vien(sv_id, nguoi_thay_doi)
        return _ok(data)
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.get("/sinh-vien/<sv_id>/bang-diem")
def api_bang_diem_sv(sv_id: str):
    hoc_ky_id = (request.args.get("hoc_ky_id") or "").strip()
    try:
        data = bang_diem_sinh_vien(sv_id, hoc_ky_id)
        return _ok(data)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.get("/tai-khoan")
def api_list_tai_khoan():
    keyword = (request.args.get("search") or "").strip()
    vai_tro = (request.args.get("vai_tro") or "").strip()
    is_active = request.args.get("is_active")
    active_value = None if is_active is None or is_active == "" else (is_active == "1")

    try:
        data = list_tai_khoan(keyword, vai_tro, active_value)
        return _ok(data)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.put("/tai-khoan/<tai_khoan_id>/khoa")
def api_khoa_tai_khoan(tai_khoan_id: str):
    try:
        return _ok(khoa_tai_khoan(tai_khoan_id, False))
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.put("/tai-khoan/<tai_khoan_id>/mo-khoa")
def api_mo_khoa_tai_khoan(tai_khoan_id: str):
    try:
        return _ok(khoa_tai_khoan(tai_khoan_id, True))
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.put("/tai-khoan/<tai_khoan_id>/reset-mat-khau")
def api_reset_mk(tai_khoan_id: str):
    payload = request.get_json(silent=True) or {}
    mk_moi = (payload.get("mat_khau_moi") or "123456").strip()
    try:
        return _ok(reset_mat_khau(tai_khoan_id, mk_moi))
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.put("/tai-khoan/<tai_khoan_id>/vai-tro")
def api_doi_vai_tro(tai_khoan_id: str):
    payload = request.get_json(silent=True) or {}
    vai_tro_moi = (payload.get("vai_tro") or "").strip()
    try:
        return _ok(doi_vai_tro(tai_khoan_id, vai_tro_moi))
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.get("/lhp")
def api_list_lhp():
    hoc_ky_id = (request.args.get("hoc_ky_id") or "").strip()
    giang_vien_id = (request.args.get("giang_vien_id") or "").strip()
    try:
        return _ok(list_lhp(hoc_ky_id, giang_vien_id))
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.post("/lhp")
def api_create_lhp():
    payload = request.get_json(silent=True) or {}
    try:
        return _ok(create_lhp(payload), 201)
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.put("/lhp/<lhp_id>")
def api_update_lhp(lhp_id: str):
    payload = request.get_json(silent=True) or {}
    try:
        return _ok(update_lhp(lhp_id, payload))
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.delete("/lhp/<lhp_id>")
def api_delete_lhp(lhp_id: str):
    try:
        return _ok(delete_lhp(lhp_id))
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.get("/lhp/<lhp_id>/danh-sach-sv")
def api_lhp_students(lhp_id: str):
    try:
        return _ok(list_lhp_students(lhp_id))
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.post("/lhp/<lhp_id>/sinh-vien")
def api_add_sv_lhp(lhp_id: str):
    payload = request.get_json(silent=True) or {}
    sinh_vien_id = (payload.get("sinh_vien_id") or "").strip()
    if not sinh_vien_id:
        return _error("Thieu sinh_vien_id", 400)

    try:
        return _ok(add_student_to_lhp(lhp_id, sinh_vien_id), 201)
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.delete("/lhp/<lhp_id>/sinh-vien/<sv_id>")
def api_remove_sv_lhp(lhp_id: str, sv_id: str):
    try:
        return _ok(remove_student_from_lhp(lhp_id, sv_id))
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.put("/lhp/<lhp_id>/mo-nhap-diem")
def api_mo_nhap_diem(lhp_id: str):
    try:
        return _ok(mo_khoa_nhap_diem(lhp_id, True))
    except Exception as exc:
        return _error(str(exc), 500)


@sinh_vien_bp.put("/lhp/<lhp_id>/khoa-nhap-diem")
def api_khoa_nhap_diem(lhp_id: str):
    try:
        return _ok(mo_khoa_nhap_diem(lhp_id, False))
    except Exception as exc:
        return _error(str(exc), 500)
