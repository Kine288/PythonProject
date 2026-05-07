"""Auth API routes."""

from __future__ import annotations

from flask import Blueprint, jsonify, request

from services.auth_service import change_password, login, logout


auth_bp = Blueprint("auth", __name__, url_prefix="/api/auth")


@auth_bp.post("/login")
def login_route():
    data = request.get_json(silent=True) or {}
    identifier = (data.get("identifier") or data.get("email") or "").strip()
    password = data.get("password") or ""

    try:
        result = login(identifier, password)
        return jsonify({"success": True, "data": result}), 200
    except ValueError as exc:
        return jsonify({"success": False, "message": str(exc)}), 400
    except Exception:
        return jsonify({"success": False, "message": "Loi he thong"}), 500


@auth_bp.post("/logout")
def logout_route():
    data = request.get_json(silent=True) or {}
    tai_khoan_id = (data.get("tai_khoan_id") or "").strip()

    try:
        result = logout(tai_khoan_id)
        return jsonify({"success": True, "data": result}), 200
    except Exception:
        return jsonify({"success": False, "message": "Loi he thong"}), 500


@auth_bp.post("/doi-mat-khau")
def change_password_route():
    data = request.get_json(silent=True) or {}
    tai_khoan_id = (data.get("tai_khoan_id") or "").strip()
    old_password = data.get("mat_khau_cu") or data.get("old_password") or ""
    new_password = data.get("mat_khau_moi") or data.get("new_password") or ""

    try:
        result = change_password(tai_khoan_id, old_password, new_password)
        return jsonify({"success": True, "data": result}), 200
    except ValueError as exc:
        return jsonify({"success": False, "message": str(exc)}), 400
    except Exception:
        return jsonify({"success": False, "message": "Loi he thong"}), 500
