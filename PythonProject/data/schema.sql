-- Schema for Student Management System (Faculty scope)
-- MySQL 8.0+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS admin_log;
DROP TABLE IF EXISTS lich_su_ho_so;
DROP TABLE IF EXISTS ket_qua_hoc_ky;
DROP TABLE IF EXISTS yeu_cau_sua_diem;
DROP TABLE IF EXISTS audit_diem;
DROP TABLE IF EXISTS lich_su_hoc_mon;
DROP TABLE IF EXISTS ds_lhp;
DROP TABLE IF EXISTS lop_hoc_phan;
DROP TABLE IF EXISTS mon_hoc;
DROP TABLE IF EXISTS hoc_ky;
DROP TABLE IF EXISTS sinh_vien;
DROP TABLE IF EXISTS lop_sinh_hoat;
DROP TABLE IF EXISTS nien_khoa;
DROP TABLE IF EXISTS giang_vien;
DROP TABLE IF EXISTS khoa_bo_mon;
DROP TABLE IF EXISTS tai_khoan;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE tai_khoan (
    tai_khoan_id VARCHAR(32) PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    mat_khau_hash VARCHAR(255) NOT NULL,
    vai_tro ENUM('ADMIN','GIAO_VU','GIANG_VIEN','SINH_VIEN') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    lan_dang_nhap_cuoi DATETIME
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE khoa_bo_mon (
    khoa_id VARCHAR(32) PRIMARY KEY,
    ten_khoa VARCHAR(100) NOT NULL,
    ma_khoa VARCHAR(20) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE giang_vien (
    giang_vien_id VARCHAR(32) PRIMARY KEY,
    tai_khoan_id VARCHAR(32) UNIQUE NOT NULL,
    ma_gv VARCHAR(20) UNIQUE NOT NULL,
    ho_ten VARCHAR(100) NOT NULL,
    hoc_vi VARCHAR(50),
    hoc_ham VARCHAR(50),
    khoa_id VARCHAR(32),
    so_dien_thoai VARCHAR(15),
    FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan(tai_khoan_id),
    FOREIGN KEY (khoa_id) REFERENCES khoa_bo_mon(khoa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE nien_khoa (
    nien_khoa_id VARCHAR(32) PRIMARY KEY,
    ten_nien_khoa VARCHAR(20) NOT NULL,
    nam_bat_dau INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lop_sinh_hoat (
    lop_id VARCHAR(32) PRIMARY KEY,
    ma_lop VARCHAR(20) UNIQUE NOT NULL,
    ten_lop VARCHAR(50) NOT NULL,
    nien_khoa_id VARCHAR(32),
    khoa_id VARCHAR(32),
    FOREIGN KEY (nien_khoa_id) REFERENCES nien_khoa(nien_khoa_id),
    FOREIGN KEY (khoa_id) REFERENCES khoa_bo_mon(khoa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sinh_vien (
    sinh_vien_id VARCHAR(32) PRIMARY KEY,
    tai_khoan_id VARCHAR(32) UNIQUE NOT NULL,
    msv VARCHAR(20) UNIQUE NOT NULL,
    ho_ten VARCHAR(100) NOT NULL,
    ngay_sinh DATE,
    gioi_tinh ENUM('Nam','Nu','Khac'),
    lop_id VARCHAR(32),
    trang_thai ENUM('DANG_HOC','BAO_LUU','TOT_NGHIEP','BUOC_THOI_HOC') DEFAULT 'DANG_HOC',
    FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan(tai_khoan_id),
    FOREIGN KEY (lop_id) REFERENCES lop_sinh_hoat(lop_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE hoc_ky (
    hoc_ky_id VARCHAR(32) PRIMARY KEY,
    ten_hoc_ky VARCHAR(50) NOT NULL,
    nam_hoc VARCHAR(20) NOT NULL,
    ky_hoc INT NOT NULL,
    is_hien_tai BOOLEAN DEFAULT FALSE,
    ngay_bat_dau DATE,
    ngay_ket_thuc DATE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mon_hoc (
    mon_hoc_id VARCHAR(32) PRIMARY KEY,
    ma_mon VARCHAR(20) UNIQUE NOT NULL,
    ten_mon VARCHAR(100) NOT NULL,
    so_tin_chi INT NOT NULL,
    tinh_gpa BOOLEAN DEFAULT TRUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lop_hoc_phan (
    lhp_id VARCHAR(32) PRIMARY KEY,
    ma_lhp VARCHAR(30) UNIQUE NOT NULL,
    mon_hoc_id VARCHAR(32) NOT NULL,
    hoc_ky_id VARCHAR(32) NOT NULL,
    giang_vien_id VARCHAR(32),
    ty_le_cc DECIMAL(5,2) NOT NULL,
    ty_le_gk DECIMAL(5,2) NOT NULL,
    ty_le_ck DECIMAL(5,2) NOT NULL,
    trang_thai ENUM('MO','DANG_NHAP','CHO_DUYET','DA_DUYET','DONG') DEFAULT 'MO',
    cong_nhap_diem_mo BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (mon_hoc_id) REFERENCES mon_hoc(mon_hoc_id),
    FOREIGN KEY (hoc_ky_id) REFERENCES hoc_ky(hoc_ky_id),
    FOREIGN KEY (giang_vien_id) REFERENCES giang_vien(giang_vien_id),
    CONSTRAINT chk_lhp_ty_le_100 CHECK (ROUND(ty_le_cc + ty_le_gk + ty_le_ck, 2) = 100.00)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ds_lhp (
    ds_lhp_id VARCHAR(32) PRIMARY KEY,
    lhp_id VARCHAR(32) NOT NULL,
    sinh_vien_id VARCHAR(32) NOT NULL,
    diem_cc DECIMAL(4,1),
    diem_gk DECIMAL(4,1),
    diem_ck DECIMAL(4,1),
    diem_tong DECIMAL(4,1),
    trang_thai_diem ENUM('CHUA_NHAP','NHAP_NHAP','CHO_DUYET','DA_DUYET') DEFAULT 'CHUA_NHAP',
    UNIQUE KEY uk_lhp_sv (lhp_id, sinh_vien_id),
    FOREIGN KEY (lhp_id) REFERENCES lop_hoc_phan(lhp_id),
    FOREIGN KEY (sinh_vien_id) REFERENCES sinh_vien(sinh_vien_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lich_su_hoc_mon (
    ls_id VARCHAR(32) PRIMARY KEY,
    sinh_vien_id VARCHAR(32) NOT NULL,
    mon_hoc_id VARCHAR(32) NOT NULL,
    lhp_id VARCHAR(32) NOT NULL,
    hoc_ky_id VARCHAR(32) NOT NULL,
    lan_hoc INT DEFAULT 1,
    FOREIGN KEY (sinh_vien_id) REFERENCES sinh_vien(sinh_vien_id),
    FOREIGN KEY (mon_hoc_id) REFERENCES mon_hoc(mon_hoc_id),
    FOREIGN KEY (lhp_id) REFERENCES lop_hoc_phan(lhp_id),
    FOREIGN KEY (hoc_ky_id) REFERENCES hoc_ky(hoc_ky_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE audit_diem (
    audit_id VARCHAR(32) PRIMARY KEY,
    ds_lhp_id VARCHAR(32) NOT NULL,
    tai_khoan_id VARCHAR(32) NOT NULL,
    truoc_thay_doi JSON,
    sau_thay_doi JSON,
    ly_do TEXT,
    thoi_diem DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ds_lhp_id) REFERENCES ds_lhp(ds_lhp_id),
    FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan(tai_khoan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE yeu_cau_sua_diem (
    yc_id VARCHAR(32) PRIMARY KEY,
    ds_lhp_id VARCHAR(32) NOT NULL,
    giang_vien_id VARCHAR(32) NOT NULL,
    ly_do TEXT NOT NULL,
    trang_thai ENUM('CHO_XU_LY','CHAP_THUAN','TU_CHOI') DEFAULT 'CHO_XU_LY',
    giao_vu_xu_ly VARCHAR(32),
    ghi_chu_giao_vu TEXT,
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ds_lhp_id) REFERENCES ds_lhp(ds_lhp_id),
    FOREIGN KEY (giang_vien_id) REFERENCES giang_vien(giang_vien_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ket_qua_hoc_ky (
    kqhk_id VARCHAR(32) PRIMARY KEY,
    sinh_vien_id VARCHAR(32) NOT NULL,
    hoc_ky_id VARCHAR(32) NOT NULL,
    gpa_hk_he10 DECIMAL(4,2),
    gpa_hk_he4 DECIMAL(4,2),
    gpa_tich_luy_he10 DECIMAL(4,2),
    gpa_tich_luy_he4 DECIMAL(4,2),
    tong_tin_chi_dat INT DEFAULT 0,
    xep_loai VARCHAR(20),
    muc_canh_bao INT DEFAULT 0,
    UNIQUE KEY uk_sv_hk (sinh_vien_id, hoc_ky_id),
    FOREIGN KEY (sinh_vien_id) REFERENCES sinh_vien(sinh_vien_id),
    FOREIGN KEY (hoc_ky_id) REFERENCES hoc_ky(hoc_ky_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lich_su_ho_so (
    ls_hs_id VARCHAR(32) PRIMARY KEY,
    sinh_vien_id VARCHAR(32) NOT NULL,
    nguoi_thay_doi VARCHAR(32) NOT NULL,
    truoc_thay_doi JSON,
    sau_thay_doi JSON,
    thoi_diem DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sinh_vien_id) REFERENCES sinh_vien(sinh_vien_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE admin_log (
    log_id VARCHAR(32) PRIMARY KEY,
    tai_khoan_id VARCHAR(32) NOT NULL,
    hanh_dong VARCHAR(100) NOT NULL,
    doi_tuong_loai VARCHAR(50),
    doi_tuong_id VARCHAR(32),
    du_lieu JSON,
    thoi_diem DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tai_khoan_id) REFERENCES tai_khoan(tai_khoan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_sv_msv ON sinh_vien(msv);
CREATE INDEX idx_lhp_hoc_ky ON lop_hoc_phan(hoc_ky_id);
CREATE INDEX idx_lhp_giang_vien ON lop_hoc_phan(giang_vien_id);
CREATE INDEX idx_kqhk_sv ON ket_qua_hoc_ky(sinh_vien_id);
CREATE INDEX idx_kqhk_hk ON ket_qua_hoc_ky(hoc_ky_id);
CREATE INDEX idx_yc_trang_thai ON yeu_cau_sua_diem(trang_thai);
CREATE INDEX idx_audit_diem_ds_lhp ON audit_diem(ds_lhp_id);

-- Note for implementation:
-- For student accounts, set tai_khoan.email = msv and login identifier = msv.
-- This matches the requirement: "Tk cua sv se la ma sinh vien luon".
