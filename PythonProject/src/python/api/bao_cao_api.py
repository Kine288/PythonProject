from flask import Blueprint, jsonify, request, send_file

from services.xuat_bao_cao import (
    lay_ds_canh_bao,
    thong_ke_xep_loai,
    xuat_excel_canh_bao,
    xuat_excel_tong_ket_hoc_vu,
    xuat_pdf_bang_diem_ca_nhan,
)

bao_cao_bp = Blueprint("bao_cao_api", __name__, url_prefix="/api/bao-cao")


def _error(message: str, status: int = 400):
    return jsonify({"success": False, "message": message}), status


def _ok(data=None, message: str = ""):
    return jsonify({"success": True, "message": message, "data": data})


@bao_cao_bp.get("/canh-bao")
def api_ds_canh_bao():
    hoc_ky_id = (request.args.get("hoc_ky_id") or "").strip()
    muc = (request.args.get("muc_canh_bao") or "").strip()

    if not hoc_ky_id:
        return _error("Thieu hoc_ky_id")

    muc_value = None
    if muc:
        try:
            muc_value = int(muc)
        except ValueError:
            return _error("muc_canh_bao khong hop le")

    try:
        data = lay_ds_canh_bao(hoc_ky_id, muc_value)
        return _ok(data)
    except Exception as exc:
        return _error(f"Khong the tai danh sach canh bao: {exc}", 500)


@bao_cao_bp.get("/thong-ke-xep-loai")
@bao_cao_bp.get("/xep-loai-khoa")
def api_thong_ke_xep_loai():
    hoc_ky_id = (request.args.get("hoc_ky_id") or "").strip()
    if not hoc_ky_id:
        return _error("Thieu hoc_ky_id")

    try:
        data = thong_ke_xep_loai(hoc_ky_id)
        return _ok(data)
    except Exception as exc:
        return _error(f"Khong the thong ke xep loai: {exc}", 500)


@bao_cao_bp.post("/export/canh-bao-excel")
@bao_cao_bp.get("/canh-bao-excel/<hoc_ky_id>")
def api_export_canh_bao_excel(hoc_ky_id: str = ""):
    payload = request.get_json(silent=True) or {}
    hoc_ky_id = hoc_ky_id or (payload.get("hoc_ky_id") or request.args.get("hoc_ky_id") or "").strip()
    muc = payload.get("muc_canh_bao", request.args.get("muc_canh_bao"))

    if not hoc_ky_id:
        return _error("Thieu hoc_ky_id")

    if muc is not None and muc != "":
        try:
            muc = int(muc)
        except (TypeError, ValueError):
            return _error("muc_canh_bao khong hop le")
    else:
        muc = None

    try:
        file_path = xuat_excel_canh_bao(hoc_ky_id, muc)
        return send_file(
            file_path,
            as_attachment=True,
            download_name=f"canh_bao_{hoc_ky_id}.xlsx",
            mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        )
    except Exception as exc:
        return _error(f"Khong the xuat Excel: {exc}", 500)


@bao_cao_bp.post("/export/tong-ket-excel")
@bao_cao_bp.get("/tong-ket-excel/<hoc_ky_id>")
def api_export_tong_ket_excel(hoc_ky_id: str = ""):
    payload = request.get_json(silent=True) or {}
    hoc_ky_id = hoc_ky_id or (payload.get("hoc_ky_id") or request.args.get("hoc_ky_id") or "").strip()
    lop_id = (payload.get("lop_id") or request.args.get("lop_id") or "").strip() or None

    if not hoc_ky_id:
        return _error("Thieu hoc_ky_id")

    try:
        file_path = xuat_excel_tong_ket_hoc_vu(hoc_ky_id, lop_id)
        return send_file(
            file_path,
            as_attachment=True,
            download_name=f"tong_ket_hoc_vu_{hoc_ky_id}.xlsx",
            mimetype="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        )
    except Exception as exc:
        return _error(f"Khong the xuat Excel tong ket: {exc}", 500)


@bao_cao_bp.post("/export/bang-diem-pdf")
@bao_cao_bp.get("/bang-diem-pdf/<sinh_vien_id>")
def api_export_bang_diem_pdf(sinh_vien_id: str = ""):
    payload = request.get_json(silent=True) or {}
    sinh_vien_id = sinh_vien_id or (payload.get("sinh_vien_id") or request.args.get("sinh_vien_id") or "").strip()

    if not sinh_vien_id:
        return _error("Thieu sinh_vien_id")

    try:
        file_path = xuat_pdf_bang_diem_ca_nhan(sinh_vien_id)
        return send_file(
            file_path,
            as_attachment=True,
            download_name=f"bang_diem_{sinh_vien_id}.pdf",
            mimetype="application/pdf",
        )
    except ValueError as exc:
        return _error(str(exc), 404)
    except Exception as exc:
        return _error(f"Khong the xuat PDF: {exc}", 500)
