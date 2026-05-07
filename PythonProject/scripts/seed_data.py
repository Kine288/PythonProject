"""Seed data for the Student Management System.

Usage:
	python scripts/seed_data.py

Assumptions:
- Database and tables from data/schema.sql already exist.
- Student login identifier is exactly msv (stored in tai_khoan.email).
"""

from __future__ import annotations

from datetime import datetime
import hashlib
from pathlib import Path
import sys
from typing import Dict, List, Tuple


PROJECT_ROOT = Path(__file__).resolve().parents[1]
if str(PROJECT_ROOT) not in sys.path:
	sys.path.append(str(PROJECT_ROOT))

from config.db_config import get_database_connection


def hash_password(raw: str) -> str:
	return hashlib.sha256(raw.encode("utf-8")).hexdigest()


def upsert(cursor, sql: str, params: Tuple) -> None:
	cursor.execute(sql, params)


IDS = {
	"khoa_cntt": "20000000000000000000000000000001",
	"hk_1": "22000000000000000000000000000001",
	"hk_2": "22000000000000000000000000000002",
	"hk_3": "22000000000000000000000000000003",
	"nien_khoa": {
		"2021-2025": "21000000000000000000000000000001",
		"2022-2026": "21000000000000000000000000000002",
		"2023-2027": "21000000000000000000000000000003",
		"2024-2028": "21000000000000000000000000000004",
	},
	"lop": {
		"CNTT-K2": "23000000000000000000000000000001",
		"CNTT-K3": "23000000000000000000000000000002",
		"CNTT-K4": "23000000000000000000000000000003",
		"CNTT-K5": "23000000000000000000000000000004",
	},
	"tai_khoan": {
		"admin1": "24000000000000000000000000000001",
		"admin2": "24000000000000000000000000000004",
		"giaovu1": "24000000000000000000000000000002",
		"giaovu2": "24000000000000000000000000000003",
		"giaovu3": "24000000000000000000000000000005",
		"gv01": "24000000000000000000000000000011",
		"gv02": "24000000000000000000000000000012",
		"gv03": "24000000000000000000000000000013",
		"gv04": "24000000000000000000000000000014",
		"gv05": "24000000000000000000000000000015",
		"gv06": "24000000000000000000000000000016",
		"gv07": "24000000000000000000000000000017",
		"gv08": "24000000000000000000000000000018",
		"gv09": "24000000000000000000000000000019",
		"gv10": "24000000000000000000000000000020",
	},
	"giang_vien": {
		"gv01": "25000000000000000000000000000001",
		"gv02": "25000000000000000000000000000002",
		"gv03": "25000000000000000000000000000003",
		"gv04": "25000000000000000000000000000004",
		"gv05": "25000000000000000000000000000005",
		"gv06": "25000000000000000000000000000006",
		"gv07": "25000000000000000000000000000007",
		"gv08": "25000000000000000000000000000008",
		"gv09": "25000000000000000000000000000009",
		"gv10": "25000000000000000000000000000010",
	},
	"giao_vu": {
		"gvu01": "25500000000000000000000000000001",
		"gvu02": "25500000000000000000000000000002",
		"gvu03": "25500000000000000000000000000003",
	},
	"mon_hoc": {
		"CNTT101": "26000000000000000000000000000001",
		"CNTT102": "26000000000000000000000000000002",
		"CNTT201": "26000000000000000000000000000003",
		"CNTT202": "26000000000000000000000000000004",
		"GDTC001": "26000000000000000000000000000005",
		"CNTT301": "26000000000000000000000000000006",
	},
	"lhp": {
		"LHP01": "27000000000000000000000000000001",
		"LHP02": "27000000000000000000000000000002",
		"LHP03": "27000000000000000000000000000003",
		"LHP04": "27000000000000000000000000000004",
	},
}


def seed_catalog(cursor) -> None:
	upsert(
		cursor,
		"""
		INSERT INTO khoa_bo_mon (khoa_id, ten_khoa, ma_khoa)
		VALUES (%s, %s, %s)
		ON DUPLICATE KEY UPDATE ten_khoa = VALUES(ten_khoa), ma_khoa = VALUES(ma_khoa)
		""",
		(IDS["khoa_cntt"], "Khoa Cong nghe thong tin", "CNTT"),
	)

	nien_khoa_rows = [
		(IDS["nien_khoa"]["2021-2025"], "2021-2025", 2021),
		(IDS["nien_khoa"]["2022-2026"], "2022-2026", 2022),
		(IDS["nien_khoa"]["2023-2027"], "2023-2027", 2023),
		(IDS["nien_khoa"]["2024-2028"], "2024-2028", 2024),
	]
	for row in nien_khoa_rows:
		upsert(
			cursor,
			"""
			INSERT INTO nien_khoa (nien_khoa_id, ten_nien_khoa, nam_bat_dau)
			VALUES (%s, %s, %s)
			ON DUPLICATE KEY UPDATE ten_nien_khoa = VALUES(ten_nien_khoa), nam_bat_dau = VALUES(nam_bat_dau)
			""",
			row,
		)

	lop_rows = [
		(IDS["lop"]["CNTT-K2"], "CNTT-K2", "Lop CNTT K2", IDS["nien_khoa"]["2021-2025"], IDS["khoa_cntt"]),
		(IDS["lop"]["CNTT-K3"], "CNTT-K3", "Lop CNTT K3", IDS["nien_khoa"]["2022-2026"], IDS["khoa_cntt"]),
		(IDS["lop"]["CNTT-K4"], "CNTT-K4", "Lop CNTT K4", IDS["nien_khoa"]["2023-2027"], IDS["khoa_cntt"]),
		(IDS["lop"]["CNTT-K5"], "CNTT-K5", "Lop CNTT K5", IDS["nien_khoa"]["2024-2028"], IDS["khoa_cntt"]),
	]
	for row in lop_rows:
		upsert(
			cursor,
			"""
			INSERT INTO lop_sinh_hoat (lop_id, ma_lop, ten_lop, nien_khoa_id, khoa_id)
			VALUES (%s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE ten_lop = VALUES(ten_lop), nien_khoa_id = VALUES(nien_khoa_id), khoa_id = VALUES(khoa_id)
			""",
			row,
		)

	hoc_ky_rows = [
		(IDS["hk_1"], "HK1 2023-2024", "2023-2024", 1, False, "2023-09-01", "2024-01-15"),
		(IDS["hk_2"], "HK2 2023-2024", "2023-2024", 2, True, "2024-01-22", "2024-05-31"),
		(IDS["hk_3"], "HK1 2024-2025", "2024-2025", 1, False, "2024-09-01", "2025-01-15"),
	]
	for row in hoc_ky_rows:
		upsert(
			cursor,
			"""
			INSERT INTO hoc_ky (hoc_ky_id, ten_hoc_ky, nam_hoc, ky_hoc, is_hien_tai, ngay_bat_dau, ngay_ket_thuc)
			VALUES (%s, %s, %s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE
				ten_hoc_ky = VALUES(ten_hoc_ky),
				nam_hoc = VALUES(nam_hoc),
				ky_hoc = VALUES(ky_hoc),
				is_hien_tai = VALUES(is_hien_tai),
				ngay_bat_dau = VALUES(ngay_bat_dau),
				ngay_ket_thuc = VALUES(ngay_ket_thuc)
			""",
			row,
		)

	mon_rows = [
		(IDS["mon_hoc"]["CNTT101"], "CNTT101", "Lap trinh co ban", 3, True),
		(IDS["mon_hoc"]["CNTT102"], "CNTT102", "Co so du lieu", 3, True),
		(IDS["mon_hoc"]["CNTT201"], "CNTT201", "Lap trinh huong doi tuong", 3, True),
		(IDS["mon_hoc"]["CNTT202"], "CNTT202", "Mang may tinh", 3, True),
		(IDS["mon_hoc"]["GDTC001"], "GDTC001", "Giao duc the chat", 2, False),
		(IDS["mon_hoc"]["CNTT301"], "CNTT301", "Ky thuat phan mem", 3, True),
	]
	for row in mon_rows:
		upsert(
			cursor,
			"""
			INSERT INTO mon_hoc (mon_hoc_id, ma_mon, ten_mon, so_tin_chi, tinh_gpa)
			VALUES (%s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE ten_mon = VALUES(ten_mon), so_tin_chi = VALUES(so_tin_chi), tinh_gpa = VALUES(tinh_gpa)
			""",
			row,
		)


def seed_accounts_and_staff(cursor) -> Dict[str, str]:
	account_rows = [
		(IDS["tai_khoan"]["admin1"], "admin1@qlsv.edu.vn", hash_password("1"), "ADMIN", True),
		(IDS["tai_khoan"]["admin2"], "admin2@qlsv.edu.vn", hash_password("1"), "ADMIN", True),
		(IDS["tai_khoan"]["giaovu1"], "giaovu1@qlsv.edu.vn", hash_password("1"), "GIAO_VU", True),
		(IDS["tai_khoan"]["giaovu2"], "giaovu2@qlsv.edu.vn", hash_password("1"), "GIAO_VU", True),
		(IDS["tai_khoan"]["giaovu3"], "giaovu3@qlsv.edu.vn", hash_password("1"), "GIAO_VU", True),
		(IDS["tai_khoan"]["gv01"], "gv01@qlsv.edu.vn", hash_password("1"), "GIANG_VIEN", True),
		(IDS["tai_khoan"]["gv02"], "gv02@qlsv.edu.vn", hash_password("1"), "GIANG_VIEN", True),
		(IDS["tai_khoan"]["gv03"], "gv03@qlsv.edu.vn", hash_password("1"), "GIANG_VIEN", True),
		(IDS["tai_khoan"]["gv04"], "gv04@qlsv.edu.vn", hash_password("1"), "GIANG_VIEN", True),
		(IDS["tai_khoan"]["gv05"], "gv05@qlsv.edu.vn", hash_password("1"), "GIANG_VIEN", True),
		(IDS["tai_khoan"]["gv06"], "gv06@qlsv.edu.vn", hash_password("1"), "GIANG_VIEN", True),
		(IDS["tai_khoan"]["gv07"], "gv07@qlsv.edu.vn", hash_password("1"), "GIANG_VIEN", True),
		(IDS["tai_khoan"]["gv08"], "gv08@qlsv.edu.vn", hash_password("1"), "GIANG_VIEN", True),
		(IDS["tai_khoan"]["gv09"], "gv09@qlsv.edu.vn", hash_password("1"), "GIANG_VIEN", True),
		(IDS["tai_khoan"]["gv10"], "gv10@qlsv.edu.vn", hash_password("1"), "GIANG_VIEN", True),
	]

	for row in account_rows:
		upsert(
			cursor,
			"""
			INSERT INTO tai_khoan (tai_khoan_id, email, mat_khau_hash, vai_tro, is_active)
			VALUES (%s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE mat_khau_hash = VALUES(mat_khau_hash), vai_tro = VALUES(vai_tro), is_active = VALUES(is_active)
			""",
			row,
		)

	giao_vu_rows = [
		(IDS["giao_vu"]["gvu01"], IDS["tai_khoan"]["giaovu1"], "GVU01", "Giao vu 01", IDS["khoa_cntt"], "0909000001"),
		(IDS["giao_vu"]["gvu02"], IDS["tai_khoan"]["giaovu2"], "GVU02", "Giao vu 02", IDS["khoa_cntt"], "0909000002"),
		(IDS["giao_vu"]["gvu03"], IDS["tai_khoan"]["giaovu3"], "GVU03", "Giao vu 03", IDS["khoa_cntt"], "0909000003"),
	]
	for row in giao_vu_rows:
		upsert(
			cursor,
			"""
			INSERT INTO giao_vu (giao_vu_id, tai_khoan_id, ma_giao_vu, ho_ten, khoa_id, so_dien_thoai)
			VALUES (%s, %s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE
				ho_ten = VALUES(ho_ten),
				khoa_id = VALUES(khoa_id),
				so_dien_thoai = VALUES(so_dien_thoai)
			""",
			row,
		)

	giang_vien_rows = [
		(IDS["giang_vien"]["gv01"], IDS["tai_khoan"]["gv01"], "gv01", "Giang vien 01", "Thac si", None, IDS["khoa_cntt"], "0911000001"),
		(IDS["giang_vien"]["gv02"], IDS["tai_khoan"]["gv02"], "gv02", "Giang vien 02", "Thac si", None, IDS["khoa_cntt"], "0911000002"),
		(IDS["giang_vien"]["gv03"], IDS["tai_khoan"]["gv03"], "gv03", "Giang vien 03", "Tien si", None, IDS["khoa_cntt"], "0911000003"),
		(IDS["giang_vien"]["gv04"], IDS["tai_khoan"]["gv04"], "gv04", "Giang vien 04", "Tien si", None, IDS["khoa_cntt"], "0911000004"),
		(IDS["giang_vien"]["gv05"], IDS["tai_khoan"]["gv05"], "gv05", "Giang vien 05", "Thac si", None, IDS["khoa_cntt"], "0911000005"),
		(IDS["giang_vien"]["gv06"], IDS["tai_khoan"]["gv06"], "gv06", "ThS giang vien 06", "Thac si", None, IDS["khoa_cntt"], "0911000006"),
		(IDS["giang_vien"]["gv07"], IDS["tai_khoan"]["gv07"], "gv07", "Tien si giang vien 07", "Tien si", None, IDS["khoa_cntt"], "0911000007"),
		(IDS["giang_vien"]["gv08"], IDS["tai_khoan"]["gv08"], "gv08", "Giang vien 08", "Thac si", None, IDS["khoa_cntt"], "0911000008"),
		(IDS["giang_vien"]["gv09"], IDS["tai_khoan"]["gv09"], "gv09", "Giang vien 09", "Tien si", None, IDS["khoa_cntt"], "0911000009"),
		(IDS["giang_vien"]["gv10"], IDS["tai_khoan"]["gv10"], "gv10", "Giang vien 10", "Thac si", None, IDS["khoa_cntt"], "0911000010"),
	]
	for row in giang_vien_rows:
		upsert(
			cursor,
			"""
			INSERT INTO giang_vien (giang_vien_id, tai_khoan_id, ma_gv, ho_ten, hoc_vi, hoc_ham, khoa_id, so_dien_thoai)
			VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE ho_ten = VALUES(ho_ten), hoc_vi = VALUES(hoc_vi), hoc_ham = VALUES(hoc_ham), khoa_id = VALUES(khoa_id), so_dien_thoai = VALUES(so_dien_thoai)
			""",
			row,
		)

	return {
		"gv01": IDS["giang_vien"]["gv01"],
		"gv02": IDS["giang_vien"]["gv02"],
		"gv03": IDS["giang_vien"]["gv03"],
		"gv04": IDS["giang_vien"]["gv04"],
		"gv05": IDS["giang_vien"]["gv05"],
		"gv06": IDS["giang_vien"]["gv06"],
		"gv07": IDS["giang_vien"]["gv07"],
		"gv08": IDS["giang_vien"]["gv08"],
		"gv09": IDS["giang_vien"]["gv09"],
		"gv10": IDS["giang_vien"]["gv10"],
	}


def build_students() -> List[Dict[str, str]]:
	students: List[Dict[str, str]] = []
	classes = [
		("CNTT-K2", "725101", 8),
		("CNTT-K3", "735101", 8),
		("CNTT-K4", "745101", 7),
		("CNTT-K5", "755101", 7),
	]
	index = 1
	for class_name, prefix, count in classes:
		for i in range(1, count + 1):
			msv = f"{prefix}{i:03d}"
			students.append(
				{
					"tai_khoan_id": f"3000000000000000000000000000{index:04d}",
					"sinh_vien_id": f"3100000000000000000000000000{index:04d}",
					"msv": msv,
					"ho_ten": f"Sinh vien {msv}",
					"ngay_sinh": f"200{(index % 5) + 1}-{(index % 12) + 1:02d}-{(index % 27) + 1:02d}",
					"gioi_tinh": "Nam" if index % 2 else "Nu",
					"lop_id": IDS["lop"][class_name],
				}
			)
			index += 1
	return students


def seed_students(cursor, students: List[Dict[str, str]]) -> None:
	for sv in students:
		# Student account = student code (msv)
		upsert(
			cursor,
			"""
			INSERT INTO tai_khoan (tai_khoan_id, email, mat_khau_hash, vai_tro, is_active)
			VALUES (%s, %s, %s, 'SINH_VIEN', TRUE)
			ON DUPLICATE KEY UPDATE
				email = VALUES(email),
				mat_khau_hash = VALUES(mat_khau_hash),
				is_active = VALUES(is_active)
			""",
			(sv["tai_khoan_id"], sv["msv"], hash_password(sv["msv"])),
		)

		upsert(
			cursor,
			"""
			INSERT INTO sinh_vien (sinh_vien_id, tai_khoan_id, msv, ho_ten, ngay_sinh, gioi_tinh, lop_id, trang_thai)
			VALUES (%s, %s, %s, %s, %s, %s, %s, 'DANG_HOC')
			ON DUPLICATE KEY UPDATE
				ho_ten = VALUES(ho_ten),
				ngay_sinh = VALUES(ngay_sinh),
				gioi_tinh = VALUES(gioi_tinh),
				lop_id = VALUES(lop_id),
				trang_thai = VALUES(trang_thai)
			""",
			(
				sv["sinh_vien_id"],
				sv["tai_khoan_id"],
				sv["msv"],
				sv["ho_ten"],
				sv["ngay_sinh"],
				sv["gioi_tinh"],
				sv["lop_id"],
			),
		)


def seed_lhp(cursor, gv_map: Dict[str, str]) -> None:
	lhp_rows = [
		(
			IDS["lhp"]["LHP01"],
			"CNTT101-HK2-2024",
			IDS["mon_hoc"]["CNTT101"],
			IDS["hk_2"],
			gv_map["gv01"],
			10.0,
			30.0,
			60.0,
			"DA_DUYET",
			False,
		),
		(
			IDS["lhp"]["LHP02"],
			"CNTT102-HK2-2024",
			IDS["mon_hoc"]["CNTT102"],
			IDS["hk_2"],
			gv_map["gv02"],
			10.0,
			40.0,
			50.0,
			"CHO_DUYET",
			False,
		),
		(
			IDS["lhp"]["LHP03"],
			"CNTT201-HK2-2024",
			IDS["mon_hoc"]["CNTT201"],
			IDS["hk_2"],
			gv_map["gv03"],
			20.0,
			30.0,
			50.0,
			"DANG_NHAP",
			True,
		),
		(
			IDS["lhp"]["LHP04"],
			"CNTT202-HK2-2024",
			IDS["mon_hoc"]["CNTT202"],
			IDS["hk_2"],
			gv_map["gv04"],
			10.0,
			30.0,
			60.0,
			"MO",
			False,
		),
	]
	for row in lhp_rows:
		upsert(
			cursor,
			"""
			INSERT INTO lop_hoc_phan (
				lhp_id, ma_lhp, mon_hoc_id, hoc_ky_id, giang_vien_id,
				ty_le_cc, ty_le_gk, ty_le_ck, trang_thai, cong_nhap_diem_mo
			)
			VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE
				mon_hoc_id = VALUES(mon_hoc_id),
				hoc_ky_id = VALUES(hoc_ky_id),
				giang_vien_id = VALUES(giang_vien_id),
				ty_le_cc = VALUES(ty_le_cc),
				ty_le_gk = VALUES(ty_le_gk),
				ty_le_ck = VALUES(ty_le_ck),
				trang_thai = VALUES(trang_thai),
				cong_nhap_diem_mo = VALUES(cong_nhap_diem_mo)
			""",
			row,
		)


def insert_or_update_ds_lhp(cursor, ds_lhp_id: str, lhp_id: str, sinh_vien_id: str, diem_cc, diem_gk, diem_ck, diem_tong, trang_thai_diem: str) -> None:
	upsert(
		cursor,
		"""
		INSERT INTO ds_lhp (
			ds_lhp_id, lhp_id, sinh_vien_id, diem_cc, diem_gk, diem_ck, diem_tong, trang_thai_diem
		) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
		ON DUPLICATE KEY UPDATE
			diem_cc = VALUES(diem_cc),
			diem_gk = VALUES(diem_gk),
			diem_ck = VALUES(diem_ck),
			diem_tong = VALUES(diem_tong),
			trang_thai_diem = VALUES(trang_thai_diem)
		""",
		(ds_lhp_id, lhp_id, sinh_vien_id, diem_cc, diem_gk, diem_ck, diem_tong, trang_thai_diem),
	)


def seed_registrations_and_scores(cursor, students: List[Dict[str, str]]) -> None:
	first_10 = students[:10]
	next_10 = students[10:20]

	# LHP01: DA_DUYET, score distribution for BR2-BR5 testing.
	diem_tong_lhp01 = [
		9.2,
		8.8,
		8.6,
		8.2,
		7.9,
		7.1,
		6.8,
		6.0,
		4.5,
		3.2,
	]

	for i, sv in enumerate(first_10, start=1):
		dt = diem_tong_lhp01[i - 1]
		cc = round(min(10.0, dt + 0.4), 1)
		gk = round(min(10.0, dt + 0.2), 1)
		ck = round(max(0.0, min(10.0, (dt - 0.1))), 1)
		insert_or_update_ds_lhp(
			cursor,
			f"3200000000000000000000000000{i:04d}",
			IDS["lhp"]["LHP01"],
			sv["sinh_vien_id"],
			cc,
			gk,
			ck,
			dt,
			"DA_DUYET",
		)
		upsert(
			cursor,
			"""
			INSERT INTO lich_su_hoc_mon (ls_id, sinh_vien_id, mon_hoc_id, lhp_id, hoc_ky_id, lan_hoc)
			VALUES (%s, %s, %s, %s, %s, 1)
			ON DUPLICATE KEY UPDATE lhp_id = VALUES(lhp_id), hoc_ky_id = VALUES(hoc_ky_id)
			""",
			(
				f"3300000000000000000000000000{i:04d}",
				sv["sinh_vien_id"],
				IDS["mon_hoc"]["CNTT101"],
				IDS["lhp"]["LHP01"],
				IDS["hk_2"],
			),
		)

	# LHP02: CHO_DUYET, full component scores for 10 students.
	for i, sv in enumerate(first_10, start=1):
		cc = round(6.0 + (i % 4) * 0.8, 1)
		gk = round(5.5 + (i % 5) * 0.7, 1)
		ck = round(5.0 + (i % 6) * 0.8, 1)
		insert_or_update_ds_lhp(
			cursor,
			f"3400000000000000000000000000{i:04d}",
			IDS["lhp"]["LHP02"],
			sv["sinh_vien_id"],
			cc,
			gk,
			ck,
			None,
			"CHO_DUYET",
		)
		upsert(
			cursor,
			"""
			INSERT INTO lich_su_hoc_mon (ls_id, sinh_vien_id, mon_hoc_id, lhp_id, hoc_ky_id, lan_hoc)
			VALUES (%s, %s, %s, %s, %s, 1)
			ON DUPLICATE KEY UPDATE lhp_id = VALUES(lhp_id), hoc_ky_id = VALUES(hoc_ky_id)
			""",
			(
				f"3500000000000000000000000000{i:04d}",
				sv["sinh_vien_id"],
				IDS["mon_hoc"]["CNTT102"],
				IDS["lhp"]["LHP02"],
				IDS["hk_2"],
			),
		)

	# LHP03: DANG_NHAP, partial draft for 5/10 students (CC/GK only, CK empty).
	for i, sv in enumerate(next_10, start=1):
		if i <= 5:
			cc = round(6.5 + i * 0.4, 1)
			gk = round(5.8 + i * 0.5, 1)
			ck = None
			trang_thai_diem = "NHAP_NHAP"
		else:
			cc = None
			gk = None
			ck = None
			trang_thai_diem = "CHUA_NHAP"

		insert_or_update_ds_lhp(
			cursor,
			f"3600000000000000000000000000{i:04d}",
			IDS["lhp"]["LHP03"],
			sv["sinh_vien_id"],
			cc,
			gk,
			ck,
			None,
			trang_thai_diem,
		)
		upsert(
			cursor,
			"""
			INSERT INTO lich_su_hoc_mon (ls_id, sinh_vien_id, mon_hoc_id, lhp_id, hoc_ky_id, lan_hoc)
			VALUES (%s, %s, %s, %s, %s, 1)
			ON DUPLICATE KEY UPDATE lhp_id = VALUES(lhp_id), hoc_ky_id = VALUES(hoc_ky_id)
			""",
			(
				f"3700000000000000000000000000{i:04d}",
				sv["sinh_vien_id"],
				IDS["mon_hoc"]["CNTT201"],
				IDS["lhp"]["LHP03"],
				IDS["hk_2"],
			),
		)


def seed_admin_logs(cursor) -> None:
	upsert(
		cursor,
		"""
		INSERT INTO admin_log (log_id, tai_khoan_id, hanh_dong, doi_tuong_loai, doi_tuong_id, du_lieu, thoi_diem)
		VALUES (%s, %s, %s, %s, %s, %s, %s)
		ON DUPLICATE KEY UPDATE hanh_dong = VALUES(hanh_dong), du_lieu = VALUES(du_lieu)
		""",
		(
			"38000000000000000000000000000001",
			IDS["tai_khoan"]["admin1"],
			"SEED_INITIAL_DATA",
			"SYSTEM",
			"INITIAL",
			'{"source":"scripts/seed_data.py"}',
			datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
		),
	)


def main() -> None:
	conn = get_database_connection()
	if conn is None:
		raise RuntimeError("Khong ket noi duoc CSDL")

	students = build_students()
	try:
		with conn.cursor() as cursor:
			seed_catalog(cursor)
			gv_map = seed_accounts_and_staff(cursor)
			seed_students(cursor, students)
			seed_lhp(cursor, gv_map)
			seed_registrations_and_scores(cursor, students)
			seed_admin_logs(cursor)

		conn.commit()
		print("Seed data thanh cong: 2 admin, 3 giao vu, 10 giang vien, 30 sinh vien, 4 LHP.")
	except Exception:
		conn.rollback()
		raise
	finally:
		conn.close()


if __name__ == "__main__":
	main()
