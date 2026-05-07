from pathlib import Path
import sys

PROJECT_ROOT = Path(__file__).resolve().parents[1]
if str(PROJECT_ROOT) not in sys.path:
    sys.path.append(str(PROJECT_ROOT))

from config.db_config import get_database_connection


def main() -> None:
    conn = get_database_connection()
    if conn is None:
        raise RuntimeError("Khong ket noi duoc CSDL")

    with conn.cursor() as cur:
        cur.execute("SELECT vai_tro, COUNT(*) c FROM tai_khoan GROUP BY vai_tro ORDER BY vai_tro")
        print("accounts_by_role", cur.fetchall())

        cur.execute("SELECT COUNT(*) c FROM sinh_vien")
        print("student_count", cur.fetchall())

        cur.execute(
            """
            SELECT COUNT(*) c
            FROM sinh_vien
            WHERE LENGTH(msv) = 9
              AND LEFT(msv, 1) = '7'
              AND SUBSTRING(msv, 2, 1) IN ('2', '3', '4', '5')
              AND SUBSTRING(msv, 3, 4) = '5101'
            """
        )
        print("msv_format_ok", cur.fetchall())

        cur.execute("SELECT ma_lhp, trang_thai, cong_nhap_diem_mo FROM lop_hoc_phan ORDER BY ma_lhp")
        print("lhp_status", cur.fetchall())

        cur.execute(
            """
            SELECT
                SUM(diem_tong >= 8.5) AS a,
                SUM(diem_tong BETWEEN 7.0 AND 8.4) AS b,
                SUM(diem_tong BETWEEN 5.5 AND 6.9) AS c,
                SUM(diem_tong BETWEEN 4.0 AND 5.4) AS d,
                SUM(diem_tong < 4.0) AS f
            FROM ds_lhp
            WHERE lhp_id = '27000000000000000000000000000001'
            """
        )
        print("lhp01_band", cur.fetchall())

        cur.execute(
            """
            SELECT
                SUM(diem_cc IS NOT NULL AND diem_gk IS NOT NULL AND diem_ck IS NULL) AS partial_cnt,
                COUNT(*) AS total
            FROM ds_lhp
            WHERE lhp_id = '27000000000000000000000000000003'
            """
        )
        print("lhp03_partial", cur.fetchall())

    conn.close()


if __name__ == "__main__":
    main()
