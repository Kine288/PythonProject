import json
import math
import uuid

from config.db_config import get_database_connection

TRANG_THAI_GIANG_VIEN_CHO_DUYET = 1
TRANG_THAI_GIAO_VU_DA_KHOA = 2


def _new_uuid():
	return uuid.uuid4().hex


def tinh_diem_tong(diem_cc, diem_gk, diem_ck, ty_le_cc, ty_le_gk, ty_le_ck):
	tong = (diem_cc * ty_le_cc + diem_gk * ty_le_gk + diem_ck * ty_le_ck) / 100

	# round() does bankers rounding; use math to ensure .5 rounds up
	tong_lam_tron = math.floor(tong * 10 + 0.5) / 10
	return tong_lam_tron


def validate_ty_le(ty_le_cc, ty_le_gk, ty_le_ck):
	tong = ty_le_cc + ty_le_gk + ty_le_ck
	if tong != 100:
		raise ValueError(f"Tong trong so phai = 100%, hien tai = {tong}%")


def duyet_va_tinh_diem_lhp(lhp_id, giao_vu_id):
	conn = get_database_connection()
	if not conn:
		raise ConnectionError("Khong ket noi duoc database")

	try:
		conn.begin()
		cursor = conn.cursor()

		cursor.execute(
			"""
			SELECT ty_le_cc, ty_le_gk, ty_le_ck
			FROM lop_hoc_phan
			WHERE lhp_id = %s
			""",
			(lhp_id,),
		)
		lhp = cursor.fetchone()
		if not lhp:
			raise ValueError(f"Khong tim thay LHP: {lhp_id}")

		validate_ty_le(lhp["ty_le_cc"], lhp["ty_le_gk"], lhp["ty_le_ck"])

		cursor.execute(
			"""
			SELECT ds_lhp_id, sinh_vien_id, diem_cc, diem_gk, diem_ck, diem_tong
			FROM ds_lhp
			WHERE lhp_id = %s
			""",
			(lhp_id,),
		)
		ds_diem = cursor.fetchall()

		for row in ds_diem:
			diem_tong = tinh_diem_tong(
				row["diem_cc"],
				row["diem_gk"],
				row["diem_ck"],
				lhp["ty_le_cc"],
				lhp["ty_le_gk"],
				lhp["ty_le_ck"],
			)

			cursor.execute(
				"""
				UPDATE ds_lhp
				SET diem_tong = %s
				WHERE ds_lhp_id = %s
				""",
				(diem_tong, row["ds_lhp_id"]),
			)

			cursor.execute(
				"""
				INSERT INTO audit_diem
					(audit_id, ds_lhp_id, nguoi_thay_doi_id, loai_thay_doi,
					 gia_tri_cu, gia_tri_moi)
				VALUES (%s, %s, %s, %s, %s, %s)
				""",
				(
					_new_uuid(),
					row["ds_lhp_id"],
					giao_vu_id,
					"DUYET_DIEM",
					json.dumps({"diem_tong": row["diem_tong"]}),
					json.dumps({"diem_tong": diem_tong}),
				),
			)

		cursor.execute(
			"""
			UPDATE lop_hoc_phan
			SET trang_thai_giao_vu = %s
			WHERE lhp_id = %s
			""",
			(TRANG_THAI_GIAO_VU_DA_KHOA, lhp_id),
		)

		conn.commit()
		return {"success": True, "so_luong": len(ds_diem)}

	except Exception as e:
		conn.rollback()
		raise e
	finally:
		conn.close()


def them_sinh_vien_vao_lhp(sinh_vien_id, lhp_id):
	conn = get_database_connection()
	if not conn:
		raise ConnectionError("Khong ket noi duoc database")

	try:
		conn.begin()
		cursor = conn.cursor()

		cursor.execute(
			"""
			SELECT mon_hoc_id
			FROM lop_hoc_phan
			WHERE lhp_id = %s
			""",
			(lhp_id,),
		)
		lhp = cursor.fetchone()
		if not lhp:
			raise ValueError(f"Khong tim thay LHP: {lhp_id}")

		cursor.execute(
			"""
			SELECT COALESCE(MAX(lan_hoc), 0) AS lan_hoc
			FROM lich_su_hoc_mon
			WHERE sinh_vien_id = %s AND mon_hoc_id = %s
			""",
			(sinh_vien_id, lhp["mon_hoc_id"]),
		)
		lan_hoc = cursor.fetchone()["lan_hoc"] + 1

		ds_lhp_id = _new_uuid()
		cursor.execute(
			"""
			INSERT INTO ds_lhp (ds_lhp_id, sinh_vien_id, lhp_id)
			VALUES (%s, %s, %s)
			""",
			(ds_lhp_id, sinh_vien_id, lhp_id),
		)

		cursor.execute(
			"""
			INSERT INTO lich_su_hoc_mon
				(lich_su_id, sinh_vien_id, mon_hoc_id, ds_lhp_id, lan_hoc)
			VALUES (%s, %s, %s, %s, %s)
			""",
			(_new_uuid(), sinh_vien_id, lhp["mon_hoc_id"], ds_lhp_id, lan_hoc),
		)

		conn.commit()
		return {"success": True, "ds_lhp_id": ds_lhp_id}

	except Exception as e:
		conn.rollback()
		raise e
	finally:
		conn.close()
