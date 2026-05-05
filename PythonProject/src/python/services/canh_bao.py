from services.tinh_gpa import dem_hoc_ky_da_qua


def xac_dinh_muc_canh_bao(sinh_vien_id: str, gpa_tich_luy_he_4: float) -> int:
	"""BR5 warning level 1 threshold by completed semester count.

	Current schema stores one field `muc_canh_bao` in `ket_qua_hoc_ky`, so this function
	focuses on threshold-based level 1 evaluation.
	"""
	so_hoc_ky_da_qua = dem_hoc_ky_da_qua(sinh_vien_id)

	if so_hoc_ky_da_qua <= 1:
		nguong = 1.2
	elif so_hoc_ky_da_qua == 2:
		nguong = 1.4
	else:
		nguong = 1.6

	return 1 if gpa_tich_luy_he_4 < nguong else 0
