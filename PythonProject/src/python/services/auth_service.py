"""Authentication service for login/logout/password change."""

from __future__ import annotations

import hashlib
from typing import Dict

from core.database import db_cursor, execute_transaction


def _hash_password(raw_password: str) -> str:
    return hashlib.sha256(raw_password.encode("utf-8")).hexdigest()


def login(identifier: str, password: str) -> Dict:
    if not identifier or not password:
        raise ValueError("Thieu thong tin dang nhap")

    with db_cursor() as (_, cursor):
        cursor.execute(
            """
            SELECT tai_khoan_id, email, vai_tro, is_active
            FROM tai_khoan
            WHERE email = %s
            LIMIT 1
            """,
            (identifier,),
        )
        user = cursor.fetchone()

    if user is None:
        raise ValueError("Tai khoan khong ton tai")
    if int(user["is_active"]) != 1:
        raise ValueError("Tai khoan da bi khoa")

    expected_hash = _hash_password(password)
    with db_cursor(commit=True) as (_, cursor):
        cursor.execute(
            """
            SELECT tai_khoan_id
            FROM tai_khoan
            WHERE tai_khoan_id = %s AND mat_khau_hash = %s
            LIMIT 1
            """,
            (user["tai_khoan_id"], expected_hash),
        )
        matched = cursor.fetchone()
        if matched is None:
            raise ValueError("Sai mat khau")

        cursor.execute(
            "UPDATE tai_khoan SET lan_dang_nhap_cuoi = NOW() WHERE tai_khoan_id = %s",
            (user["tai_khoan_id"],),
        )

    return {
        "tai_khoan_id": user["tai_khoan_id"],
        "identifier": user["email"],
        "vai_tro": user["vai_tro"],
    }


def logout(_: str) -> Dict:
    # Stateless API for now.
    return {"message": "Dang xuat thanh cong"}


def change_password(tai_khoan_id: str, old_password: str, new_password: str) -> Dict:
    if not tai_khoan_id or not old_password or not new_password:
        raise ValueError("Thieu du lieu doi mat khau")

    old_hash = _hash_password(old_password)
    new_hash = _hash_password(new_password)

    def _tx(cursor):
        cursor.execute(
            """
            SELECT tai_khoan_id
            FROM tai_khoan
            WHERE tai_khoan_id = %s AND mat_khau_hash = %s
            LIMIT 1
            """,
            (tai_khoan_id, old_hash),
        )
        row = cursor.fetchone()
        if row is None:
            raise ValueError("Mat khau hien tai khong dung")

        cursor.execute(
            "UPDATE tai_khoan SET mat_khau_hash = %s WHERE tai_khoan_id = %s",
            (new_hash, tai_khoan_id),
        )

    execute_transaction(_tx)
    return {"message": "Doi mat khau thanh cong"}
