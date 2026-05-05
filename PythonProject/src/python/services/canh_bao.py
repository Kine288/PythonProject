from config.db_config import get_database_connection
from services.tinh_gpa import dem_hoc_ky_da_qua


def _nguong_muc_1(so_hoc_ky_da_qua: int) -> float:
	if so_hoc_ky_da_qua <= 1:
		return 1.2
	if so_hoc_ky_da_qua == 2:
		return 1.4
	return 1.6


def _lay_lich_su_canh_bao(sinh_vien_id: str, hoc_ky_id_hien_tai: str):
	conn = get_database_connection()
	if not conn:
		raise ConnectionError("Khong ket noi duoc database")

	try:
		with conn.cursor() as cursor:
			cursor.execute(
				"""
				SELECT kq.hoc_ky_id, kq.muc_canh_bao
				FROM ket_qua_hoc_ky kq
				JOIN hoc_ky hk ON hk.hoc_ky_id = kq.hoc_ky_id
				WHERE kq.sinh_vien_id = %s
				  AND kq.hoc_ky_id <> %s
				ORDER BY hk.ngay_ket_thuc DESC, hk.ten_hoc_ky DESC
				""",
				(sinh_vien_id, hoc_ky_id_hien_tai),
			)
			return cursor.fetchall()
	finally:
		conn.close()


def xac_dinh_muc_canh_bao(sinh_vien_id: str, hoc_ky_id: str, gpa_tich_luy_he_4: float) -> int:
	"""BR5: 3 levels (muc 1, muc 2, buoc thoi hoc)."""
	so_hoc_ky_da_qua = dem_hoc_ky_da_qua(sinh_vien_id)
	nguong = _nguong_muc_1(so_hoc_ky_da_qua)
	bi_vi_pham_muc_1 = gpa_tich_luy_he_4 < nguong

	if not bi_vi_pham_muc_1:
		return 0

	history = _lay_lich_su_canh_bao(sinh_vien_id, hoc_ky_id)
	prev_level = int(history[0]["muc_canh_bao"]) if history else 0

	# Vi pham 2 hoc ky lien tiep -> muc 2.
	current_level = 2 if prev_level >= 1 else 1

	tong_lan_canh_bao = len([row for row in history if int(row["muc_canh_bao"] or 0) > 0]) + 1
	bi_muc_2_hai_lan_lien_tiep = current_level == 2 and prev_level == 2

	if bi_muc_2_hai_lan_lien_tiep or tong_lan_canh_bao >= 3:
		return 3

	return current_level
