import uuid

from config.db_config import get_database_connection


def _new_uuid():
	return uuid.uuid4().hex


def _tinh_gpa_tu_danh_sach(rows):
	tong_diem = 0.0
	tong_tin_chi = 0
	for row in rows:
		diem_tong = row.get("diem_tong")
		so_tin_chi = row.get("so_tin_chi")
		if diem_tong is None or so_tin_chi is None:
			continue
		tong_diem += diem_tong * so_tin_chi
		tong_tin_chi += so_tin_chi
	if tong_tin_chi == 0:
		return 0.0, 0
	return round(tong_diem / tong_tin_chi, 2), tong_tin_chi


def lay_quy_tac_quy_doi(diem_tong):
	conn = get_database_connection()
	if not conn:
		raise ConnectionError("Khong ket noi duoc database")

	try:
		cursor = conn.cursor()
		cursor.execute(
			"""
			SELECT diem_chu, diem_he_4
			FROM quy_tac_quy_doi
			WHERE %s >= diem_tu AND %s < diem_den
			ORDER BY diem_tu DESC
			LIMIT 1
			""",
			(diem_tong, diem_tong),
		)
		return cursor.fetchone()
	finally:
		conn.close()


def tinh_gpa_hoc_ky(sinh_vien_id, hoc_ky_id):
	conn = get_database_connection()
	if not conn:
		raise ConnectionError("Khong ket noi duoc database")

	try:
		cursor = conn.cursor()
		cursor.execute(
			"""
			SELECT d.diem_tong, mh.so_tin_chi
			FROM ds_lhp d
			JOIN lop_hoc_phan lhp ON lhp.lhp_id = d.lhp_id
			JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
			WHERE d.sinh_vien_id = %s
			  AND lhp.hoc_ky_id = %s
			  AND mh.is_tinh_gpa = 1
			""",
			(sinh_vien_id, hoc_ky_id),
		)
		rows = cursor.fetchall()
		return _tinh_gpa_tu_danh_sach(rows)
	finally:
		conn.close()


def tinh_gpa_tich_luy(sinh_vien_id):
	conn = get_database_connection()
	if not conn:
		raise ConnectionError("Khong ket noi duoc database")

	try:
		cursor = conn.cursor()
		cursor.execute(
			"""
			SELECT d.diem_tong, mh.so_tin_chi
			FROM lich_su_hoc_mon lshm
			JOIN (
				SELECT sinh_vien_id, mon_hoc_id, MAX(lan_hoc) AS lan_cuoi
				FROM lich_su_hoc_mon
				WHERE sinh_vien_id = %s
				GROUP BY mon_hoc_id
			) t ON lshm.sinh_vien_id = t.sinh_vien_id
			   AND lshm.mon_hoc_id = t.mon_hoc_id
			   AND lshm.lan_hoc = t.lan_cuoi
			JOIN ds_lhp d ON d.ds_lhp_id = lshm.ds_lhp_id
			JOIN lop_hoc_phan lhp ON lhp.lhp_id = d.lhp_id
			JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
			WHERE lshm.sinh_vien_id = %s
			  AND mh.is_tinh_gpa = 1
			""",
			(sinh_vien_id, sinh_vien_id),
		)
		rows = cursor.fetchall()
		return _tinh_gpa_tu_danh_sach(rows)
	finally:
		conn.close()


def luu_ket_qua_hoc_ky(sinh_vien_id, hoc_ky_id, gpa_he_10, gpa_he_4, gpa_tich_luy_he_10, gpa_tich_luy_he_4, tong_tin_chi):
	conn = get_database_connection()
	if not conn:
		raise ConnectionError("Khong ket noi duoc database")

	try:
		cursor = conn.cursor()
		cursor.execute(
			"""
			INSERT INTO ket_qua_hoc_ky
				(kqhk_id, sinh_vien_id, hoc_ky_id, gpa_he_10, gpa_he_4,
				 gpa_tich_luy_he_10, gpa_tich_luy_he_4, tong_tin_chi)
			VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE
				gpa_he_10 = VALUES(gpa_he_10),
				gpa_he_4 = VALUES(gpa_he_4),
				gpa_tich_luy_he_10 = VALUES(gpa_tich_luy_he_10),
				gpa_tich_luy_he_4 = VALUES(gpa_tich_luy_he_4),
				tong_tin_chi = VALUES(tong_tin_chi)
			""",
			(
				_new_uuid(),
				sinh_vien_id,
				hoc_ky_id,
				gpa_he_10,
				gpa_he_4,
				gpa_tich_luy_he_10,
				gpa_tich_luy_he_4,
				tong_tin_chi,
			),
		)
		conn.commit()
		return {"success": True}
	except Exception as e:
		conn.rollback()
		raise e
	finally:
		conn.close()
