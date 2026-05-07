from pathlib import Path
import sys
from datetime import datetime

PROJECT_ROOT = Path(__file__).resolve().parents[1]
PY_ROOT = PROJECT_ROOT / "src" / "python"

for p in (PROJECT_ROOT, PY_ROOT):
    if str(p) not in sys.path:
        sys.path.append(str(p))

from src.python.main import create_app
from config.db_config import get_database_connection


IDS = {
    "hoc_ky_hien_tai": "22000000000000000000000000000002",
    "lop_cntt_k5": "23000000000000000000000000000004",
    "mon_cntt301": "26000000000000000000000000000006",
    "lhp01": "27000000000000000000000000000001",
    "lhp03": "27000000000000000000000000000003",
    "admin1_tk": "24000000000000000000000000000001",
    "admin2_tk": "24000000000000000000000000000004",
    "giaovu1_tk": "24000000000000000000000000000002",
    "gv01_tk": "24000000000000000000000000000011",
    "gv01_profile": "25000000000000000000000000000001",
    "gv10_profile": "25000000000000000000000000000010",
    "sv01_profile": "31000000000000000000000000000001",
}


def call(client, method: str, url: str, json_body=None, expected_status: int = 200):
    if method == "GET":
        resp = client.get(url)
    elif method == "POST":
        resp = client.post(url, json=json_body)
    elif method == "PUT":
        resp = client.put(url, json=json_body)
    elif method == "DELETE":
        resp = client.delete(url)
    else:
        raise ValueError("Unsupported method")

    payload = resp.get_json(silent=True)
    print(method, url, resp.status_code)
    if payload is not None:
        print("  success:", payload.get("success"))

    if resp.status_code != expected_status:
        raise AssertionError(f"{method} {url} expected {expected_status} but got {resp.status_code}: {payload}")

    return payload


def main():
    app = create_app()
    created_student_id = None
    created_student_account_id = None
    created_lhp_id = None
    created_yc_id = None

    with app.test_client() as client:
        # 0) Health
        call(client, "GET", "/health")

        # 1) Auth
        call(client, "POST", "/api/auth/login", {"identifier": "admin1@qlsv.edu.vn", "password": "1"})
        call(
            client,
            "POST",
            "/api/auth/doi-mat-khau",
            {"tai_khoan_id": IDS["admin2_tk"], "mat_khau_cu": "1", "mat_khau_moi": "Admin2@1"},
        )
        call(
            client,
            "POST",
            "/api/auth/doi-mat-khau",
            {"tai_khoan_id": IDS["admin2_tk"], "mat_khau_cu": "Admin2@1", "mat_khau_moi": "1"},
        )
        call(client, "POST", "/api/auth/logout", {"tai_khoan_id": IDS["admin1_tk"]})

        # 2) Accounts
        accounts_payload = call(client, "GET", "/api/tai-khoan")
        accounts = accounts_payload.get("data") or []
        if len(accounts) < 45:
            raise AssertionError("Du lieu tai khoan it hon du kien sau seed")

        call(client, "PUT", f"/api/tai-khoan/{IDS['admin2_tk']}/khoa")
        call(client, "PUT", f"/api/tai-khoan/{IDS['admin2_tk']}/mo-khoa")
        call(client, "PUT", f"/api/tai-khoan/{IDS['admin2_tk']}/reset-mat-khau", {"mat_khau_moi": "1"})
        call(client, "PUT", f"/api/tai-khoan/{IDS['admin2_tk']}/vai-tro", {"vai_tro": "ADMIN"})

        # 3) Sinh vien CRUD basic
        ts = datetime.now().strftime("%H%M%S")
        new_msv = f"785101{ts[-3:]}"
        create_payload = call(
            client,
            "POST",
            "/api/sinh-vien",
            {
                "msv": new_msv,
                "ho_ten": "Sinh vien test smoke",
                "ngay_sinh": "2005-01-01",
                "gioi_tinh": "Nam",
                "lop_id": IDS["lop_cntt_k5"],
                "mat_khau": "1",
                "admin_tai_khoan_id": IDS["admin1_tk"],
            },
            expected_status=201,
        )
        new_sv = create_payload["data"]
        new_sv_id = new_sv["sinh_vien_id"]
        created_student_id = new_sv_id
        created_student_account_id = new_sv["tai_khoan_id"]

        call(client, "GET", f"/api/sinh-vien/{new_sv_id}")
        call(
            client,
            "PUT",
            f"/api/sinh-vien/{new_sv_id}",
            {"trang_thai": "DANG_HOC", "nguoi_thay_doi": IDS["giaovu1_tk"]},
        )
        call(client, "GET", f"/api/sinh-vien/{new_sv_id}/bang-diem")
        call(client, "GET", "/api/sinh-vien")

        # 4) LHP workflows
        call(client, "GET", "/api/lhp")
        new_lhp_code = f"SMK-{ts}"
        lhp_created = call(
            client,
            "POST",
            "/api/lhp",
            {
                "ma_lhp": new_lhp_code,
                "mon_hoc_id": IDS["mon_cntt301"],
                "hoc_ky_id": IDS["hoc_ky_hien_tai"],
                "giang_vien_id": IDS["gv10_profile"],
                "ty_le_cc": 10,
                "ty_le_gk": 30,
                "ty_le_ck": 60,
            },
            expected_status=201,
        )
        new_lhp_id = lhp_created["data"]["lhp_id"]
        created_lhp_id = new_lhp_id

        call(client, "PUT", f"/api/lhp/{new_lhp_id}", {"trang_thai": "MO"})
        call(client, "POST", f"/api/lhp/{new_lhp_id}/sinh-vien", {"sinh_vien_id": new_sv_id}, expected_status=201)
        call(client, "GET", f"/api/lhp/{new_lhp_id}/danh-sach-sv")
        call(client, "PUT", f"/api/lhp/{new_lhp_id}/mo-nhap-diem")
        call(client, "PUT", f"/api/lhp/{new_lhp_id}/khoa-nhap-diem")

        # 5) Diem workflows
        temp_rows_payload = call(client, "GET", f"/api/lhp/{created_lhp_id}/danh-sach-sv")
        temp_rows = temp_rows_payload.get("data") or []
        if not temp_rows:
            raise AssertionError("Khong co sinh vien trong LHP test")

        row = temp_rows[0]
        call(
            client,
            "PUT",
            f"/api/diem/lhp/{created_lhp_id}",
            {
                "tai_khoan_id": IDS["gv01_tk"],
                "ly_do": "Smoke update",
                "rows": [
                    {
                        "ds_lhp_id": row["ds_lhp_id"],
                        "diem_cc": 8.0,
                        "diem_gk": 7.5,
                        "diem_ck": 8.2,
                    }
                ],
            },
        )
        call(client, "POST", f"/api/diem/lhp/{created_lhp_id}/gui-duyet")
        call(client, "POST", f"/api/diem/lhp/{created_lhp_id}/duyet", {"tai_khoan_id": IDS["giaovu1_tk"]})
        call(client, "POST", f"/api/diem/lhp/{created_lhp_id}/tu-choi")

        ds_lhp_id = row["ds_lhp_id"]
        call(
            client,
            "POST",
            "/api/diem/yeu-cau-sua",
            {"ds_lhp_id": ds_lhp_id, "giang_vien_id": IDS["gv01_profile"], "ly_do": "Smoke yeu cau sua"},
        )
        requests_payload = call(client, "GET", "/api/diem/yeu-cau-sua")
        yc_id = (requests_payload.get("data") or [])[0]["yc_id"]
        created_yc_id = yc_id
        call(
            client,
            "PUT",
            f"/api/diem/yeu-cau-sua/{yc_id}",
            {"giao_vu_id": IDS["giaovu1_tk"], "chap_thuan": True, "ghi_chu": "Smoke approve"},
        )

        # 6) GPA + Bao cao
        call(client, "POST", f"/api/gpa/tinh-hoc-ky/{IDS['hoc_ky_hien_tai']}", {})
        call(client, "GET", f"/api/gpa/ket-qua/{IDS['sv01_profile']}?hoc_ky_id={IDS['hoc_ky_hien_tai']}")
        call(client, "GET", f"/api/gpa/canh-bao/{IDS['hoc_ky_hien_tai']}")
        call(client, "GET", f"/api/bao-cao/canh-bao?hoc_ky_id={IDS['hoc_ky_hien_tai']}")
        call(client, "GET", f"/api/bao-cao/xep-loai-khoa?hoc_ky_id={IDS['hoc_ky_hien_tai']}")

        print("Smoke API completed successfully.")

    # Cleanup temporary artifacts generated by smoke tests.
    conn = get_database_connection()
    if conn is not None:
        with conn.cursor() as cursor:
            if created_yc_id:
                cursor.execute("DELETE FROM yeu_cau_sua_diem WHERE yc_id = %s", (created_yc_id,))

            if created_lhp_id:
                cursor.execute("DELETE FROM audit_diem WHERE ds_lhp_id IN (SELECT ds_lhp_id FROM ds_lhp WHERE lhp_id = %s)", (created_lhp_id,))
                cursor.execute("DELETE FROM yeu_cau_sua_diem WHERE ds_lhp_id IN (SELECT ds_lhp_id FROM ds_lhp WHERE lhp_id = %s)", (created_lhp_id,))
                cursor.execute("DELETE FROM lich_su_hoc_mon WHERE lhp_id = %s", (created_lhp_id,))
                cursor.execute("DELETE FROM ds_lhp WHERE lhp_id = %s", (created_lhp_id,))
                cursor.execute("DELETE FROM lop_hoc_phan WHERE lhp_id = %s", (created_lhp_id,))

            if created_student_id:
                cursor.execute("DELETE FROM ket_qua_hoc_ky WHERE sinh_vien_id = %s", (created_student_id,))
                cursor.execute("DELETE FROM lich_su_ho_so WHERE sinh_vien_id = %s", (created_student_id,))
                cursor.execute("DELETE FROM lich_su_hoc_mon WHERE sinh_vien_id = %s", (created_student_id,))
                cursor.execute("DELETE FROM ds_lhp WHERE sinh_vien_id = %s", (created_student_id,))
                cursor.execute("DELETE FROM sinh_vien WHERE sinh_vien_id = %s", (created_student_id,))

            if created_student_account_id:
                cursor.execute("DELETE FROM tai_khoan WHERE tai_khoan_id = %s", (created_student_account_id,))

        conn.commit()
        conn.close()


if __name__ == "__main__":
    main()
