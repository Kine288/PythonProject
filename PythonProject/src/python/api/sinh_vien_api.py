from flask import Blueprint, jsonify, request

from src.python.services.sinh_vien_service import (
    add_student_to_lhp,
    assign_lecturer,
    create_lop_hoc_phan,
    create_student,
    delete_student,
    get_student,
    list_giang_vien,
    list_hoc_ky,
    list_lhp_enrollments,
    list_lop,
    list_lop_hoc_phan,
    list_mon_hoc,
    list_students,
    remove_student_from_lhp,
    transfer_student_class,
    update_student,
)

sinh_vien_bp = Blueprint("sinh_vien_api", __name__, url_prefix="/api/sinh-vien")


def _ok(data=None, message="", status=200):
    return (
        jsonify(
            {
                "success": True,
                "message": message,
                "data": data,
            }
        ),
        status,
    )


def _error(message, status=400):
    return jsonify({"success": False, "message": message}), status


@sinh_vien_bp.get("/students")
def api_list_students():
    keyword = request.args.get("keyword", "").strip()
    lop_id = request.args.get("lop_id", "").strip()
    return _ok(list_students(keyword, lop_id))


@sinh_vien_bp.get("/students/<sinh_vien_id>")
def api_get_student(sinh_vien_id):
    data = get_student(sinh_vien_id)
    if not data:
        return _error("Khong tim thay sinh vien", 404)
    return _ok(data)


@sinh_vien_bp.post("/students")
def api_create_student():
    payload = request.get_json(silent=True) or {}
    try:
        data = create_student(payload)
        return _ok(data, "Tao sinh vien thanh cong", 201)
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(f"Khong the tao sinh vien: {exc}", 500)


@sinh_vien_bp.put("/students/<sinh_vien_id>")
def api_update_student(sinh_vien_id):
    payload = request.get_json(silent=True) or {}
    try:
        data = update_student(sinh_vien_id, payload)
        return _ok(data, "Cap nhat sinh vien thanh cong")
    except ValueError as exc:
        return _error(str(exc), 404)
    except Exception as exc:
        return _error(f"Khong the cap nhat sinh vien: {exc}", 500)


@sinh_vien_bp.delete("/students/<sinh_vien_id>")
def api_delete_student(sinh_vien_id):
    try:
        delete_student(sinh_vien_id)
        return _ok(None, "Xoa sinh vien thanh cong")
    except ValueError as exc:
        return _error(str(exc), 404)
    except Exception as exc:
        return _error(f"Khong the xoa sinh vien: {exc}", 500)


@sinh_vien_bp.post("/students/<sinh_vien_id>/transfer-class")
def api_transfer_class(sinh_vien_id):
    payload = request.get_json(silent=True) or {}
    lop_id_moi = payload.get("lop_id_moi")
    if not lop_id_moi:
        return _error("Thieu lop_id_moi", 400)

    try:
        data = transfer_student_class(sinh_vien_id, lop_id_moi)
        return _ok(data, "Chuyen lop thanh cong")
    except ValueError as exc:
        return _error(str(exc), 404)
    except Exception as exc:
        return _error(f"Khong the chuyen lop: {exc}", 500)


@sinh_vien_bp.get("/catalog/lop")
def api_catalog_lop():
    return _ok(list_lop())


@sinh_vien_bp.get("/catalog/giang-vien")
def api_catalog_giang_vien():
    return _ok(list_giang_vien())


@sinh_vien_bp.get("/catalog/mon-hoc")
def api_catalog_mon_hoc():
    return _ok(list_mon_hoc())


@sinh_vien_bp.get("/catalog/hoc-ky")
def api_catalog_hoc_ky():
    return _ok(list_hoc_ky())


@sinh_vien_bp.get("/lhp")
def api_list_lhp():
    return _ok(list_lop_hoc_phan())


@sinh_vien_bp.post("/lhp")
def api_create_lhp():
    payload = request.get_json(silent=True) or {}
    try:
        data = create_lop_hoc_phan(payload)
        return _ok(data, "Mo lop hoc phan thanh cong", 201)
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(f"Khong the mo lop hoc phan: {exc}", 500)


@sinh_vien_bp.put("/lhp/<lhp_id>/assign-lecturer")
def api_assign_lecturer(lhp_id):
    payload = request.get_json(silent=True) or {}
    giang_vien_id = payload.get("giang_vien_id")
    if not giang_vien_id:
        return _error("Thieu giang_vien_id", 400)

    try:
        data = assign_lecturer(lhp_id, giang_vien_id)
        return _ok(data, "Phan cong giang vien thanh cong")
    except ValueError as exc:
        return _error(str(exc), 404)
    except Exception as exc:
        return _error(f"Khong the phan cong giang vien: {exc}", 500)


@sinh_vien_bp.get("/lhp/<lhp_id>/students")
def api_list_lhp_students(lhp_id):
    return _ok(list_lhp_enrollments(lhp_id))


@sinh_vien_bp.post("/lhp/<lhp_id>/students")
def api_add_student_to_lhp(lhp_id):
    payload = request.get_json(silent=True) or {}
    sinh_vien_id = payload.get("sinh_vien_id")
    if not sinh_vien_id:
        return _error("Thieu sinh_vien_id", 400)

    try:
        data = add_student_to_lhp(lhp_id, sinh_vien_id)
        return _ok(data, "Them sinh vien vao LHP thanh cong", 201)
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(f"Khong the them sinh vien vao LHP: {exc}", 500)


@sinh_vien_bp.delete("/lhp/<lhp_id>/students/<sinh_vien_id>")
def api_remove_student_from_lhp(lhp_id, sinh_vien_id):
    try:
        data = remove_student_from_lhp(lhp_id, sinh_vien_id)
        return _ok(data, "Xoa sinh vien khoi LHP thanh cong")
    except ValueError as exc:
        return _error(str(exc), 400)
    except Exception as exc:
        return _error(f"Khong the xoa sinh vien khoi LHP: {exc}", 500)