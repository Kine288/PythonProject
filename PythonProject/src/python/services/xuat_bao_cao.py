import tempfile
from pathlib import Path
from typing import Dict, List, Optional

from config.db_config import get_database_connection


def _conn_or_raise():
	conn = get_database_connection()
	if not conn:
		raise ConnectionError("Khong ket noi duoc database")
	return conn


def _temp_file(suffix: str) -> str:
	output_dir = Path(tempfile.gettempdir()) / "python_project_reports"
	output_dir.mkdir(parents=True, exist_ok=True)
	handle = tempfile.NamedTemporaryFile(delete=False, suffix=suffix, dir=output_dir)
	handle.close()
	return handle.name


def lay_ds_canh_bao(hoc_ky_id: str, muc_canh_bao: Optional[int] = None) -> List[Dict]:
	conn = _conn_or_raise()
	try:
		with conn.cursor() as cursor:
			sql = """
				SELECT sv.msv, sv.ten_sv, l.ten_lop,
				       kq.gpa_tich_luy_he_4, kq.xep_loai, kq.muc_canh_bao
				FROM ket_qua_hoc_ky kq
				JOIN sinh_vien sv ON sv.sinh_vien_id = kq.sinh_vien_id
				JOIN lop l ON l.lop_id = sv.lop_id
				WHERE kq.hoc_ky_id = %s AND kq.muc_canh_bao > 0
			"""
			params = [hoc_ky_id]
			if muc_canh_bao is not None:
				sql += " AND kq.muc_canh_bao = %s"
				params.append(muc_canh_bao)
			sql += " ORDER BY kq.muc_canh_bao DESC, kq.gpa_tich_luy_he_4 ASC, sv.msv ASC"

			cursor.execute(sql, tuple(params))
			return cursor.fetchall()
	finally:
		conn.close()


def thong_ke_xep_loai(hoc_ky_id: str) -> List[Dict]:
	conn = _conn_or_raise()
	try:
		with conn.cursor() as cursor:
			cursor.execute(
				"""
				SELECT COALESCE(kq.xep_loai, 'Chua xep loai') AS xep_loai,
				       COUNT(*) AS so_luong
				FROM ket_qua_hoc_ky kq
				WHERE kq.hoc_ky_id = %s
				GROUP BY COALESCE(kq.xep_loai, 'Chua xep loai')
				""",
				(hoc_ky_id,),
			)
			rows = cursor.fetchall()
			tong = sum(int(row["so_luong"]) for row in rows)
			for row in rows:
				row["ty_le"] = round((int(row["so_luong"]) / tong) * 100, 2) if tong else 0
			return rows
	finally:
		conn.close()


def xuat_excel_canh_bao(hoc_ky_id: str, muc_canh_bao: Optional[int] = None) -> str:
	try:
		from openpyxl import Workbook
	except Exception as exc:  # pragma: no cover
		raise RuntimeError("Thieu thu vien openpyxl. Vui long cai dat openpyxl.") from exc

	data = lay_ds_canh_bao(hoc_ky_id, muc_canh_bao)
	file_path = _temp_file(".xlsx")

	wb = Workbook()
	ws = wb.active
	ws.title = "CanhBaoHocVu"
	ws.append(["MSV", "Ten sinh vien", "Lop", "GPA tich luy he 4", "Xep loai", "Muc canh bao"])

	for row in data:
		ws.append(
			[
				row.get("msv"),
				row.get("ten_sv"),
				row.get("ten_lop"),
				row.get("gpa_tich_luy_he_4"),
				row.get("xep_loai"),
				row.get("muc_canh_bao"),
			]
		)

	wb.save(file_path)
	return file_path


def _lay_bang_diem_sinh_vien(sinh_vien_id: str):
	conn = _conn_or_raise()
	try:
		with conn.cursor() as cursor:
			cursor.execute(
				"""
				SELECT sv.msv, sv.ten_sv,
				       hk.ma_hoc_ky, mh.ma_mon, mh.ten_mon, mh.so_tin_chi,
				       ds.diem_tong,
				       kq.gpa_he_4, kq.gpa_tich_luy_he_4, kq.xep_loai
				FROM ds_lhp ds
				JOIN sinh_vien sv ON sv.sinh_vien_id = ds.sinh_vien_id
				JOIN lop_hoc_phan lhp ON lhp.lhp_id = ds.lhp_id
				JOIN mon_hoc mh ON mh.mon_hoc_id = lhp.mon_hoc_id
				JOIN hoc_ky hk ON hk.hoc_ky_id = lhp.hoc_ky_id
				LEFT JOIN ket_qua_hoc_ky kq
				  ON kq.sinh_vien_id = sv.sinh_vien_id
				 AND kq.hoc_ky_id = hk.hoc_ky_id
				WHERE sv.sinh_vien_id = %s
				ORDER BY hk.ten_hoc_ky DESC, mh.ma_mon ASC
				""",
				(sinh_vien_id,),
			)
			return cursor.fetchall()
	finally:
		conn.close()


def xuat_pdf_bang_diem_ca_nhan(sinh_vien_id: str) -> str:
	try:
		from reportlab.lib.pagesizes import A4
		from reportlab.pdfgen import canvas
	except Exception as exc:  # pragma: no cover
		raise RuntimeError("Thieu thu vien reportlab. Vui long cai dat reportlab.") from exc

	rows = _lay_bang_diem_sinh_vien(sinh_vien_id)
	if not rows:
		raise ValueError("Khong tim thay bang diem sinh vien")

	file_path = _temp_file(".pdf")
	c = canvas.Canvas(file_path, pagesize=A4)
	width, height = A4

	y = height - 40
	first = rows[0]
	c.setFont("Helvetica-Bold", 14)
	c.drawString(40, y, "BANG DIEM CA NHAN")
	y -= 24
	c.setFont("Helvetica", 11)
	c.drawString(40, y, f"MSV: {first['msv']}")
	c.drawString(220, y, f"Ho ten: {first['ten_sv']}")
	y -= 24

	c.setFont("Helvetica-Bold", 10)
	c.drawString(40, y, "Hoc ky")
	c.drawString(120, y, "Mon")
	c.drawString(250, y, "So TC")
	c.drawString(320, y, "Diem tong")
	c.drawString(400, y, "GPA HK")
	c.drawString(470, y, "GPA TL")
	y -= 18
	c.setFont("Helvetica", 10)

	for row in rows:
		if y < 40:
			c.showPage()
			y = height - 40
			c.setFont("Helvetica", 10)

		c.drawString(40, y, str(row.get("ma_hoc_ky") or ""))
		c.drawString(120, y, str(row.get("ma_mon") or ""))
		c.drawString(250, y, str(row.get("so_tin_chi") or ""))
		c.drawString(320, y, str(row.get("diem_tong") or ""))
		c.drawString(400, y, str(row.get("gpa_he_4") or ""))
		c.drawString(470, y, str(row.get("gpa_tich_luy_he_4") or ""))
		y -= 16

	last = rows[0]
	y -= 8
	c.setFont("Helvetica-Bold", 11)
	c.drawString(40, y, f"Xep loai hien tai: {last.get('xep_loai') or 'Chua xep loai'}")

	c.save()
	return file_path
