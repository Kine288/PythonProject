class Diem:
	def __init__(
		self,
		ds_lhp_id,
		sinh_vien_id,
		lhp_id,
		diem_cc=None,
		diem_gk=None,
		diem_ck=None,
		diem_tong=None,
	):
		self.ds_lhp_id = ds_lhp_id
		self.sinh_vien_id = sinh_vien_id
		self.lhp_id = lhp_id
		self.diem_cc = diem_cc
		self.diem_gk = diem_gk
		self.diem_ck = diem_ck
		self.diem_tong = diem_tong

	def to_dict(self):
		return {
			"ds_lhp_id": self.ds_lhp_id,
			"sinh_vien_id": self.sinh_vien_id,
			"lhp_id": self.lhp_id,
			"diem_cc": self.diem_cc,
			"diem_gk": self.diem_gk,
			"diem_ck": self.diem_ck,
			"diem_tong": self.diem_tong,
		}
