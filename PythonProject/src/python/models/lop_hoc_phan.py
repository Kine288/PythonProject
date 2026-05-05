class LopHocPhan:
	def __init__(
		self,
		lhp_id,
		ma_lhp,
		mon_hoc_id,
		hoc_ky_id,
		giang_vien_id,
		ty_le_cc,
		ty_le_gk,
		ty_le_ck,
		trang_thai_giao_vu=1,
		trang_thai_giang_vien=0,
	):
		self.lhp_id = lhp_id
		self.ma_lhp = ma_lhp
		self.mon_hoc_id = mon_hoc_id
		self.hoc_ky_id = hoc_ky_id
		self.giang_vien_id = giang_vien_id
		self.ty_le_cc = ty_le_cc
		self.ty_le_gk = ty_le_gk
		self.ty_le_ck = ty_le_ck
		self.trang_thai_giao_vu = trang_thai_giao_vu
		self.trang_thai_giang_vien = trang_thai_giang_vien

	def validate_ty_le(self):
		tong = self.ty_le_cc + self.ty_le_gk + self.ty_le_ck
		if tong != 100:
			raise ValueError(f"Tong trong so phai = 100%, hien tai = {tong}%")

	def to_dict(self):
		return {
			"lhp_id": self.lhp_id,
			"ma_lhp": self.ma_lhp,
			"mon_hoc_id": self.mon_hoc_id,
			"hoc_ky_id": self.hoc_ky_id,
			"giang_vien_id": self.giang_vien_id,
			"ty_le_cc": self.ty_le_cc,
			"ty_le_gk": self.ty_le_gk,
			"ty_le_ck": self.ty_le_ck,
			"trang_thai_giao_vu": self.trang_thai_giao_vu,
			"trang_thai_giang_vien": self.trang_thai_giang_vien,
		}
