import uuid
from typing import Dict, List, Tuple

from config.db_config import get_database_connection


def _new_uuid() -> str:
	return uuid.uuid4().hex


def _get_conn():
	conn = get_database_connection()
	if not conn:
		raise ConnectionError("Khong ket noi duoc database")
	return conn


def _tinh_gpa_tu_danh_sach(rows: List[Dict]) -> Tuple[float, int]:
	tong_diem = 0.0
	tong_tin_chi = 0
	for row in rows:
		diem = row.get("diem", row.get("diem_tong"))
		so_tin_chi = row.get("so_tin_chi")
		if diem is None or so_tin_chi is None:
			continue
		tong_diem += float(diem) * int(so_tin_chi)
		tong_tin_chi += int(so_tin_chi)
	if tong_tin_chi == 0:
		return 0.0, 0
	return round(tong_diem / tong_tin_chi, 2), tong_tin_chi


def quy_doi_he_10_sang_he_4(diem_he_10: float) -> float:
	if diem_he_10 >= 8.5:
		return 4.0
	if diem_he_10 >= 8.0:
		return 3.5
	if diem_he_10 >= 7.0:
		return 3.0
	if diem_he_10 >= 6.5:
		return 2.5
	if diem_he_10 >= 5.5:
		return 2.0
	if diem_he_10 >= 5.0:
		return 1.5
	if diem_he_10 >= 4.0:
		return 1.0
	return 0.0


def lay_quy_tac_quy_doi(diem_tong: float):
	conn = _get_conn()
	try:
		cursor = conn.cursor()
		cursor.execute(
			"""
			SELECT diem_chu, diem_he_4
			FROM quy_tac_quy_doi
			WHERE %s >= diem_tu AND (%s < diem_den OR (%s = 10 AND diem_den = 10))
			ORDER BY diem_tu DESC
			LIMIT 1
			""",
			(diem_tong, diem_tong, diem_tong),
		)
		row = cursor.fetchone()
		if row:
			return row
		return {"diem_chu": None, "diem_he_4": quy_doi_he_10_sang_he_4(diem_tong)}
	finally:
		conn.close()


def co_mon_f(sinh_vien_id: str) -> bool:
	"""On-the-fly query used by BR4."""
	conn = _get_conn()
	try:
		cursor = conn.cursor()
		cursor.execute(
			"""
			SELECT 1
			FROM lich_su_hoc_mon lshm
			JOIN ds_lhp ds ON ds.ds_lhp_id = lshm.ds_lhp_id
			JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
			JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
			WHERE lshm.sinh_vien_id = %s
			  AND mh.is_tinh_gpa = 1
			  AND ds.diem_tong IS NOT NULL
			  AND ds.diem_tong < 4.0
			LIMIT 1
			""",
			(sinh_vien_id,),
		)
		return cursor.fetchone() is not None
	finally:
		conn.close()


def dem_hoc_ky_da_qua(sinh_vien_id: str) -> int:
	"""On-the-fly query used by BR5."""
	conn = _get_conn()
	try:
		cursor = conn.cursor()
		cursor.execute(
			"""
			SELECT COUNT(DISTINCT lhp.hoc_ky_id) AS so_hoc_ky
			FROM ds_lhp ds
			JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
			WHERE ds.sinh_vien_id = %s
			""",
			(sinh_vien_id,),
		)
		row = cursor.fetchone() or {"so_hoc_ky": 0}
		return int(row["so_hoc_ky"] or 0)
	finally:
		conn.close()


def tinh_gpa_hoc_ky(sinh_vien_id: str, hoc_ky_id: str) -> Tuple[float, float, int]:
	conn = _get_conn()
	try:
		cursor = conn.cursor()
		cursor.execute(
			"""
			SELECT ds.diem_tong AS diem, mh.so_tin_chi
			FROM ds_lhp ds
			JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
			JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
			WHERE ds.sinh_vien_id = %s
			  AND lhp.hoc_ky_id = %s
			  AND mh.is_tinh_gpa = 1
			""",
			(sinh_vien_id, hoc_ky_id),
		)
		rows = cursor.fetchall()
		gpa_he_10, tong_tin_chi = _tinh_gpa_tu_danh_sach(rows)
		gpa_he_4 = round(quy_doi_he_10_sang_he_4(gpa_he_10), 2)
		return gpa_he_10, gpa_he_4, tong_tin_chi
	finally:
		conn.close()


def tinh_gpa_tich_luy(sinh_vien_id: str) -> Tuple[float, float, int]:
	"""Apply BR3: only latest attempt per subject and is_tinh_gpa = 1."""
	conn = _get_conn()
	try:
		cursor = conn.cursor()
		cursor.execute(
			"""
			SELECT ds.diem_tong AS diem, mh.so_tin_chi
			FROM lich_su_hoc_mon lshm
			JOIN (
				SELECT sinh_vien_id, mon_hoc_id, MAX(lan_hoc) AS lan_cuoi
				FROM lich_su_hoc_mon
				WHERE sinh_vien_id = %s
				GROUP BY sinh_vien_id, mon_hoc_id
			) lan ON lan.sinh_vien_id = lshm.sinh_vien_id
			     AND lan.mon_hoc_id = lshm.mon_hoc_id
			     AND lan.lan_cuoi = lshm.lan_hoc
			JOIN ds_lhp ds ON ds.ds_lhp_id = lshm.ds_lhp_id
			JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
			JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
			WHERE lshm.sinh_vien_id = %s
			  AND mh.is_tinh_gpa = 1
			""",
			(sinh_vien_id, sinh_vien_id),
		)
		rows = cursor.fetchall()
		gpa_he_10, tong_tin_chi = _tinh_gpa_tu_danh_sach(rows)
		gpa_he_4 = round(quy_doi_he_10_sang_he_4(gpa_he_10), 2)
		return gpa_he_10, gpa_he_4, tong_tin_chi
	finally:
		conn.close()


def luu_ket_qua_hoc_ky(
	sinh_vien_id: str,
	hoc_ky_id: str,
	gpa_he_10: float,
	gpa_he_4: float,
	gpa_tich_luy_he_10: float,
	gpa_tich_luy_he_4: float,
	tong_tin_chi: int,
	xep_loai: str,
	muc_canh_bao: int,
):
	conn = _get_conn()
	try:
		cursor = conn.cursor()
		cursor.execute(
			"""
			INSERT INTO ket_qua_hoc_ky
				(kqhk_id, sinh_vien_id, hoc_ky_id, gpa_he_10, gpa_he_4,
				 gpa_tich_luy_he_10, gpa_tich_luy_he_4, tong_tin_chi,
				 xep_loai, muc_canh_bao)
			VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
			ON DUPLICATE KEY UPDATE
				gpa_he_10 = VALUES(gpa_he_10),
				gpa_he_4 = VALUES(gpa_he_4),
				gpa_tich_luy_he_10 = VALUES(gpa_tich_luy_he_10),
				gpa_tich_luy_he_4 = VALUES(gpa_tich_luy_he_4),
				tong_tin_chi = VALUES(tong_tin_chi),
				xep_loai = VALUES(xep_loai),
				muc_canh_bao = VALUES(muc_canh_bao)
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
				xep_loai,
				muc_canh_bao,
			),
		)
		conn.commit()
		return {"success": True}
	except Exception:
		conn.rollback()
		raise
	finally:
		conn.close()
