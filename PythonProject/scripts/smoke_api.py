from pathlib import Path
import sys

PROJECT_ROOT = Path(__file__).resolve().parents[1]
PY_ROOT = PROJECT_ROOT / "src" / "python"

for p in (PROJECT_ROOT, PY_ROOT):
    if str(p) not in sys.path:
        sys.path.append(str(p))

from main import create_app


def call(client, method: str, url: str, json_body=None):
    if method == "GET":
        resp = client.get(url)
    elif method == "POST":
        resp = client.post(url, json=json_body)
    elif method == "PUT":
        resp = client.put(url, json=json_body)
    else:
        raise ValueError("Unsupported method")
    print(method, url, resp.status_code)
    payload = resp.get_json(silent=True)
    if payload is not None:
        print("  keys:", list(payload.keys()))


def main():
    app = create_app()
    with app.test_client() as client:
        call(client, "GET", "/api/sinh-vien")
        call(client, "GET", "/api/lhp")
        call(client, "GET", "/api/diem/lhp/27000000000000000000000000000001")
        call(client, "POST", "/api/gpa/tinh-hoc-ky/22000000000000000000000000000002", {})
        call(client, "GET", "/api/gpa/canh-bao/22000000000000000000000000000002")
        call(client, "GET", "/api/bao-cao/xep-loai-khoa?hoc_ky_id=22000000000000000000000000000002")


if __name__ == "__main__":
    main()
