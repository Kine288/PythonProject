from dataclasses import asdict, dataclass
from datetime import date
from typing import Any, Dict, Optional


@dataclass
class SinhVien:
	sinh_vien_id: str
	tai_khoan_id: str
	msv: str
	ten_sv: str
	gioi_tinh: Optional[int]
	ngay_sinh: Optional[date]
	lop_id: str

	def to_dict(self) -> Dict[str, Any]:
		payload = asdict(self)
		if self.ngay_sinh:
			payload["ngay_sinh"] = self.ngay_sinh.isoformat()
		return payload

	@classmethod
	def from_row(cls, row: Dict[str, Any]) -> "SinhVien":
		return cls(
			sinh_vien_id=row["sinh_vien_id"],
			tai_khoan_id=row["tai_khoan_id"],
			msv=row["msv"],
			ten_sv=row["ten_sv"],
			gioi_tinh=row.get("gioi_tinh"),
			ngay_sinh=row.get("ngay_sinh"),
			lop_id=row["lop_id"],
		)
