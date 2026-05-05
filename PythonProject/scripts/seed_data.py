"""Seed minimum data for day-1 integration.

Data includes:
- base catalog (roles, faculty, school year, classes, semester, courses)
- 2 lecturers and 20 students
- 3 course sections (LHP) with lecturer assignment
- student registrations into LHP + study history
"""

from pathlib import Path
import sys
from typing import Dict, List, Tuple


PROJECT_ROOT = Path(__file__).resolve().parents[1]
if str(PROJECT_ROOT) not in sys.path:
	sys.path.append(str(PROJECT_ROOT))

from config.db_config import get_database_connection


ROLE_IDS = {
	"ADMIN": "10000000000000000000000000000001",
	"GIAO_VU": "10000000000000000000000000000002",
	"GIANG_VIEN": "10000000000000000000000000000003",
	"SINH_VIEN": "10000000000000000000000000000004",
}

KHOA_CNTT_ID = "20000000000000000000000000000001"
NIEN_KHOA_ID = "20000000000000000000000000000002"
HOC_KY_ID = "20000000000000000000000000000003"

LOP_IDS = [
	"21000000000000000000000000000001",
	"21000000000000000000000000000002",
	"21000000000000000000000000000003",
]

MON_HOC_IDS = [
	"22000000000000000000000000000001",
	"22000000000000000000000000000002",
	"22000000000000000000000000000003",
]

GV_ACCOUNT_IDS = [
	"23000000000000000000000000000001",
	"23000000000000000000000000000002",
]

GV_IDS = [
	"23100000000000000000000000000001",
	"23100000000000000000000000000002",
]

LHP_IDS = [
	"24000000000000000000000000000001",
	"24000000000000000000000000000002",
	"24000000000000000000000000000003",
]


def upsert(cursor, sql: str, params: Tuple):
	cursor.execute(sql, params)


def seed_roles(cursor):
	for role_name, role_id in ROLE_IDS.items():
		upsert(
			cursor,
			"""
			INSERT INTO vai_tro (vai_tro_id, ten_vai_tro, mo_ta)
			VALUES (%s, %s, %s)
			ON DUPLICATE KEY UPDATE ten_vai_tro = VALUES(ten_vai_tro), mo_ta = VALUES(mo_ta)
			""",
			(role_id, role_name, f"Vai tro {role_name}"),
		)


def seed_catalog(cursor):
	upsert(
		cursor,
		"""
		INSERT INTO khoa (khoa_id, ma_khoa, ten_khoa)
		VALUES (%s, %s, %s)
		ON DUPLICATE KEY UPDATE ten_khoa = VALUES(ten_khoa)
		""",
		(KHOA_CNTT_ID, "CNTT", "Khoa Cong nghe thong tin"),
	)

	upsert(
		cursor,
		"""
		INSERT INTO nien_khoa (nien_khoa_id, ma_nien_khoa, ten_nien_khoa)
		VALUES (%s, %s, %s)
		ON DUPLICATE KEY UPDATE ten_nien_khoa = VALUES(ten_nien_khoa)
		""",
		(NIEN_KHOA_ID, "2023-2027", "Nien khoa 2023-2027"),
	)

	classes = ["CNTT-A", "CNTT-B", "CNTT-C"]
	for index, lop_id in enumerate(LOP_IDS):
		upsert(
			cursor,
			"""
			INSERT INTO lop (lop_id, khoa_id, nien_khoa_id, ten_lop)
			VALUES (%s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE ten_lop = VALUES(ten_lop)
			""",
			(lop_id, KHOA_CNTT_ID, NIEN_KHOA_ID, classes[index]),
		)

	upsert(
		cursor,
		"""
		INSERT INTO hoc_ky (hoc_ky_id, ma_hoc_ky, ten_hoc_ky, ngay_bat_dau, ngay_ket_thuc, is_hien_tai)
		VALUES (%s, %s, %s, %s, %s, 1)
		ON DUPLICATE KEY UPDATE ten_hoc_ky = VALUES(ten_hoc_ky), is_hien_tai = 1
		""",
		(HOC_KY_ID, "HK2-2025", "Hoc ky 2 nam hoc 2025-2026", "2026-01-05", "2026-05-20"),
	)

	subjects = [
		(MON_HOC_IDS[0], "PY101", "Lap trinh Python", 3),
		(MON_HOC_IDS[1], "DB201", "Co so du lieu", 3),
		(MON_HOC_IDS[2], "SE202", "Cong nghe phan mem", 2),
	]

	for mon_hoc_id, ma_mon, ten_mon, so_tin_chi in subjects:
		upsert(
			cursor,
			"""
			INSERT INTO mon_hoc (mon_hoc_id, ma_mon, ten_mon, so_tin_chi, khoa_id, is_tinh_gpa)
			VALUES (%s, %s, %s, %s, %s, 1)
			ON DUPLICATE KEY UPDATE ten_mon = VALUES(ten_mon), so_tin_chi = VALUES(so_tin_chi)
			""",
			(mon_hoc_id, ma_mon, ten_mon, so_tin_chi, KHOA_CNTT_ID),
		)

	mon_map: Dict[str, str] = {}
	for ma_mon in ["PY101", "DB201", "SE202"]:
		cursor.execute("SELECT mon_hoc_id FROM mon_hoc WHERE ma_mon = %s LIMIT 1", (ma_mon,))
		row = cursor.fetchone()
		if row:
			mon_map[ma_mon] = row["mon_hoc_id"]

	cursor.execute("SELECT hoc_ky_id FROM hoc_ky WHERE ma_hoc_ky = %s LIMIT 1", ("HK2-2025",))
	hoc_ky_row = cursor.fetchone()
	hoc_ky_id = hoc_ky_row["hoc_ky_id"] if hoc_ky_row else HOC_KY_ID

	return {
		"mon_map": mon_map,
		"hoc_ky_id": hoc_ky_id,
	}


def seed_lecturers(cursor):
	gv_accounts = [
		(GV_ACCOUNT_IDS[0], "gv.nguyenvana@edu.local", "123456"),
		(GV_ACCOUNT_IDS[1], "gv.tranthib@edu.local", "123456"),
	]

	for tai_khoan_id, email, mat_khau in gv_accounts:
		upsert(
			cursor,
			"""
			INSERT INTO tai_khoan (tai_khoan_id, email, mat_khau, vai_tro_id, is_active)
			VALUES (%s, %s, %s, %s, 1)
			ON DUPLICATE KEY UPDATE email = VALUES(email), is_active = 1
			""",
			(tai_khoan_id, email, mat_khau, ROLE_IDS["GIANG_VIEN"]),
		)

	gv_profiles = [
		(GV_IDS[0], GV_ACCOUNT_IDS[0], "GV001", "Nguyen Van A", 1, "Thac si"),
		(GV_IDS[1], GV_ACCOUNT_IDS[1], "GV002", "Tran Thi B", 0, "Tien si"),
	]

	for giang_vien_id, tai_khoan_id, ma_gv, ten_gv, gioi_tinh, hoc_vi in gv_profiles:
		upsert(
			cursor,
			"""
			INSERT INTO giang_vien (giang_vien_id, tai_khoan_id, ma_gv, ten_gv, gioi_tinh, khoa_id, hoc_vi)
			VALUES (%s, %s, %s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE ten_gv = VALUES(ten_gv), hoc_vi = VALUES(hoc_vi)
			""",
			(giang_vien_id, tai_khoan_id, ma_gv, ten_gv, gioi_tinh, KHOA_CNTT_ID, hoc_vi),
		)

	gv_map: Dict[str, str] = {}
	for ma_gv in ["GV001", "GV002"]:
		cursor.execute("SELECT giang_vien_id FROM giang_vien WHERE ma_gv = %s LIMIT 1", (ma_gv,))
		row = cursor.fetchone()
		if row:
			gv_map[ma_gv] = row["giang_vien_id"]
	return gv_map


def _build_students() -> List[Dict[str, str]]:
	students: List[Dict[str, str]] = []
	for i in range(1, 21):
		account_id = f"25{i:030d}"
		student_id = f"26{i:030d}"
		students.append(
			{
				"tai_khoan_id": account_id,
				"sinh_vien_id": student_id,
				"msv": f"SV{i:03d}",
				"ten_sv": f"Sinh Vien {i:02d}",
				"gioi_tinh": 1 if i % 2 else 0,
				"ngay_sinh": f"2005-{((i % 12) + 1):02d}-{((i % 27) + 1):02d}",
				"lop_id": LOP_IDS[(i - 1) % len(LOP_IDS)],
				"email": f"sv{i:03d}@edu.local",
			}
		)
	return students


def seed_students(cursor) -> List[Dict[str, str]]:
	students = _build_students()
	for student in students:
		upsert(
			cursor,
			"""
			INSERT INTO tai_khoan (tai_khoan_id, email, mat_khau, vai_tro_id, is_active)
			VALUES (%s, %s, %s, %s, 1)
			ON DUPLICATE KEY UPDATE email = VALUES(email), is_active = 1
			""",
			(
				student["tai_khoan_id"],
				student["email"],
				"123456",
				ROLE_IDS["SINH_VIEN"],
			),
		)

		upsert(
			cursor,
			"""
			INSERT INTO sinh_vien (sinh_vien_id, tai_khoan_id, msv, ten_sv, gioi_tinh, ngay_sinh, lop_id)
			VALUES (%s, %s, %s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE ten_sv = VALUES(ten_sv), lop_id = VALUES(lop_id), ngay_sinh = VALUES(ngay_sinh)
			""",
			(
				student["sinh_vien_id"],
				student["tai_khoan_id"],
				student["msv"],
				student["ten_sv"],
				student["gioi_tinh"],
				student["ngay_sinh"],
				student["lop_id"],
			),
		)
	return students


def seed_lhp_and_registrations(
	cursor,
	students: List[Dict[str, str]],
	gv_map: Dict[str, str],
	mon_map: Dict[str, str],
	hoc_ky_id: str,
):
	lhp_rows = [
		(LHP_IDS[0], "LHP-PY-01", gv_map.get("GV001", GV_IDS[0]), mon_map.get("PY101", MON_HOC_IDS[0])),
		(LHP_IDS[1], "LHP-DB-01", gv_map.get("GV002", GV_IDS[1]), mon_map.get("DB201", MON_HOC_IDS[1])),
		(LHP_IDS[2], "LHP-SE-01", gv_map.get("GV001", GV_IDS[0]), mon_map.get("SE202", MON_HOC_IDS[2])),
	]

	for lhp_id, ma_lhp, giang_vien_id, mon_hoc_id in lhp_rows:
		upsert(
			cursor,
			"""
			INSERT INTO lop_hoc_phan (
				lhp_id, ma_lhp, giang_vien_id, mon_hoc_id, hoc_ky_id,
				ty_le_cc, ty_le_gk, ty_le_ck, trang_thai_giao_vu, trang_thai_giang_vien
			) VALUES (%s, %s, %s, %s, %s, 10, 30, 60, 1, 0)
			ON DUPLICATE KEY UPDATE giang_vien_id = VALUES(giang_vien_id), mon_hoc_id = VALUES(mon_hoc_id)
			""",
			(lhp_id, ma_lhp, giang_vien_id, mon_hoc_id, hoc_ky_id),
		)

	for index, student in enumerate(students):
		lhp_id = LHP_IDS[index % len(LHP_IDS)]
		mon_hoc_seed = ["PY101", "DB201", "SE202"][index % 3]
		mon_hoc_id = mon_map.get(mon_hoc_seed, MON_HOC_IDS[index % len(MON_HOC_IDS)])
		ds_lhp_id = f"27{index + 1:030d}"
		lich_su_id = f"28{index + 1:030d}"

		upsert(
			cursor,
			"""
			INSERT INTO ds_lhp (ds_lhp_id, lhp_id, sinh_vien_id, diem_cc, diem_gk, diem_ck, diem_tong)
			VALUES (%s, %s, %s, NULL, NULL, NULL, NULL)
			ON DUPLICATE KEY UPDATE lhp_id = VALUES(lhp_id)
			""",
			(ds_lhp_id, lhp_id, student["sinh_vien_id"]),
		)

		upsert(
			cursor,
			"""
			INSERT INTO lich_su_hoc_mon (lich_su_id, sinh_vien_id, mon_hoc_id, ds_lhp_id, lan_hoc)
			VALUES (%s, %s, %s, %s, 1)
			ON DUPLICATE KEY UPDATE ds_lhp_id = VALUES(ds_lhp_id)
			""",
			(lich_su_id, student["sinh_vien_id"], mon_hoc_id, ds_lhp_id),
		)


def main():
	conn = get_database_connection()
	if conn is None:
		raise RuntimeError("Khong ket noi duoc den CSDL")

	try:
		with conn.cursor() as cursor:
			seed_roles(cursor)
			catalog_info = seed_catalog(cursor)
			gv_map = seed_lecturers(cursor)
			students = seed_students(cursor)
			seed_lhp_and_registrations(
				cursor,
				students,
				gv_map,
				catalog_info["mon_map"],
				catalog_info["hoc_ky_id"],
			)

		conn.commit()
		print("Seed data thanh cong: 20 sinh vien, 3 LHP, phan cong giang vien va dang ky hoc.")
	except Exception:
		conn.rollback()
		raise
	finally:
		conn.close()


if __name__ == "__main__":
	main()
