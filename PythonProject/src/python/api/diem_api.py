import json
import uuid

from flask import Blueprint, jsonify, request

from config.db_config import get_database_connection
from services.tinh_diem import (
    TRANG_THAI_GIANG_VIEN_CHO_DUYET,
    duyet_va_tinh_diem_lhp,
)

diem_bp = Blueprint("diem", __name__)


def _new_uuid():
    return uuid.uuid4().hex


def _ghi_audit(cursor, ds_lhp_id, nguoi_thay_doi_id, loai_thay_doi, gia_tri_cu, gia_tri_moi):
    cursor.execute(
        """
        INSERT INTO audit_diem
            (audit_id, ds_lhp_id, nguoi_thay_doi_id, loai_thay_doi,
             gia_tri_cu, gia_tri_moi)
        VALUES (%s, %s, %s, %s, %s, %s)
        """,
        (
            _new_uuid(),
            ds_lhp_id,
            nguoi_thay_doi_id,
            loai_thay_doi,
            json.dumps(gia_tri_cu),
            json.dumps(gia_tri_moi),
        ),
    )


def _parse_score(value, field_name):
    if value is None or value == "":
        return None
    try:
        parsed = float(value)
    except (TypeError, ValueError) as exc:
        raise ValueError(f"{field_name} khong hop le") from exc
    if not (0 <= parsed <= 10):
        raise ValueError(f"{field_name} phai trong khoang 0-10")
    return parsed


def _cap_nhat_diem(data):
    conn = get_database_connection()
    if not conn:
        raise ConnectionError("Khong ket noi duoc database")

    nguoi_thay_doi_id = data.get("nguoi_thay_doi_id")
    if not nguoi_thay_doi_id:
        raise ValueError("Thieu nguoi_thay_doi_id")

    diem_cc = _parse_score(data.get("diem_cc"), "diem_cc")
    diem_gk = _parse_score(data.get("diem_gk"), "diem_gk")
    diem_ck = _parse_score(data.get("diem_ck"), "diem_ck")

    try:
        conn.begin()
        cursor = conn.cursor()

        cursor.execute(
            """
            SELECT ds_lhp_id, diem_cc, diem_gk, diem_ck
            FROM ds_lhp
            WHERE ds_lhp_id = %s
            """,
            (data["ds_lhp_id"],),
        )
        row = cursor.fetchone()
        if not row:
            raise ValueError("Khong tim thay bang diem")

        gia_tri_cu = {"diem_cc": row["diem_cc"], "diem_gk": row["diem_gk"], "diem_ck": row["diem_ck"]}
        gia_tri_moi = {"diem_cc": diem_cc, "diem_gk": diem_gk, "diem_ck": diem_ck}

        cursor.execute(
            """
            UPDATE ds_lhp
            SET diem_cc = %s, diem_gk = %s, diem_ck = %s
            WHERE ds_lhp_id = %s
            """,
            (
                diem_cc,
                diem_gk,
                diem_ck,
                data["ds_lhp_id"],
            ),
        )

        _ghi_audit(
            cursor,
            data["ds_lhp_id"],
            nguoi_thay_doi_id,
            "NHAP_DIEM",
            gia_tri_cu,
            gia_tri_moi,
        )

        conn.commit()
        return {"success": True}

    except Exception as e:
        conn.rollback()
        raise e
    finally:
        conn.close()


@diem_bp.route("/diem/luu-nhap", methods=["POST"])
def luu_nhap_diem():
    data = request.get_json() or {}
    try:
        result = _cap_nhat_diem(data)
        return jsonify(result)
    except ValueError as e:
        return jsonify({"success": False, "error": str(e)}), 400
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500


@diem_bp.route("/diem/gui-duyet", methods=["POST"])
def gui_duyet():
    data = request.get_json() or {}
    conn = get_database_connection()
    if not conn:
        return jsonify({"success": False, "error": "Khong ket noi duoc database"}), 500

    try:
        conn.begin()
        cursor = conn.cursor()
        cursor.execute(
            """
            UPDATE lop_hoc_phan
            SET trang_thai_giang_vien = %s
            WHERE lhp_id = %s
            """,
            (TRANG_THAI_GIANG_VIEN_CHO_DUYET, data.get("lhp_id")),
        )
        conn.commit()
        return jsonify({"success": True})
    except Exception as e:
        conn.rollback()
        return jsonify({"success": False, "error": str(e)}), 500
    finally:
        conn.close()


@diem_bp.route("/diem/duyet", methods=["POST"])
def duyet_diem():
    data = request.get_json() or {}
    try:
        result = duyet_va_tinh_diem_lhp(data["lhp_id"], data["giao_vu_id"])
        return jsonify(result)
    except ValueError as e:
        return jsonify({"success": False, "error": str(e)}), 400
    except Exception as e:
        return jsonify({"success": False, "error": str(e)}), 500