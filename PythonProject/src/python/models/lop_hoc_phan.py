from dataclasses import asdict, dataclass
from typing import Any, Dict


@dataclass
class LopHocPhan:
	lhp_id: str
	ma_lhp: str
	giang_vien_id: str
	mon_hoc_id: str
	hoc_ky_id: str
	ty_le_cc: int = 10
	ty_le_gk: int = 30
	ty_le_ck: int = 60
	trang_thai_giao_vu: int = 1
	trang_thai_giang_vien: int = 0

	def validate_ty_le(self) -> None:
		tong = self.ty_le_cc + self.ty_le_gk + self.ty_le_ck
		if tong != 100:
			raise ValueError(f"Tong trong so phai = 100%, hien tai = {tong}%")

	def to_dict(self) -> Dict[str, Any]:
		return asdict(self)

	@classmethod
	def from_row(cls, row: Dict[str, Any]) -> "LopHocPhan":
		return cls(
			lhp_id=row["lhp_id"],
			ma_lhp=row["ma_lhp"],
			giang_vien_id=row["giang_vien_id"],
			mon_hoc_id=row["mon_hoc_id"],
			hoc_ky_id=row["hoc_ky_id"],
			ty_le_cc=row.get("ty_le_cc", 10),
			ty_le_gk=row.get("ty_le_gk", 30),
			ty_le_ck=row.get("ty_le_ck", 60),
			trang_thai_giao_vu=row.get("trang_thai_giao_vu", 1),
			trang_thai_giang_vien=row.get("trang_thai_giang_vien", 0),
		)
