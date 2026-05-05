from services.tinh_gpa import co_mon_f


def xep_loai_hoc_luc(gpa_tich_luy_he_4: float, sinh_vien_id: str) -> str:
	"""BR4 classification with one-level demotion for Excellent/Good if any F exists."""
	if gpa_tich_luy_he_4 >= 3.6:
		xep_loai = "Xuat sac"
	elif gpa_tich_luy_he_4 >= 3.2:
		xep_loai = "Gioi"
	elif gpa_tich_luy_he_4 >= 2.5:
		xep_loai = "Kha"
	elif gpa_tich_luy_he_4 >= 2.0:
		xep_loai = "Trung binh"
	elif gpa_tich_luy_he_4 >= 1.0:
		xep_loai = "Yeu"
	else:
		xep_loai = "Kem"

	if xep_loai in ("Xuat sac", "Gioi") and co_mon_f(sinh_vien_id):
		if xep_loai == "Xuat sac":
			return "Gioi"
		return "Kha"

	return xep_loai
