-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 30, 2026 at 04:15 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_python`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_diem`
--

CREATE TABLE `audit_diem` (
  `audit_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ds_lhp_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nguoi_thay_doi_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `loai_thay_doi` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gia_tri_cu` json DEFAULT NULL,
  `gia_tri_moi` json DEFAULT NULL,
  `thoi_diem` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ds_lhp`
--

CREATE TABLE `ds_lhp` (
  `ds_lhp_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lhp_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sinh_vien_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `diem_cc` float DEFAULT NULL,
  `diem_gk` float DEFAULT NULL,
  `diem_ck` float DEFAULT NULL,
  `diem_tong` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `giang_vien`
--

CREATE TABLE `giang_vien` (
  `giang_vien_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tai_khoan_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ma_gv` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_gv` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gioi_tinh` tinyint DEFAULT NULL,
  `khoa_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hoc_vi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `giao_vu`
--

CREATE TABLE `giao_vu` (
  `giao_vu_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tai_khoan_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ma_giao_vu` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_giao_vu` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phong_ban` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hoc_ky`
--

CREATE TABLE `hoc_ky` (
  `hoc_ky_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ma_hoc_ky` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_hoc_ky` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ngay_bat_dau` date DEFAULT NULL,
  `ngay_ket_thuc` date DEFAULT NULL,
  `is_hien_tai` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ket_qua_hoc_ky`
--

CREATE TABLE `ket_qua_hoc_ky` (
  `kqhk_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sinh_vien_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hoc_ky_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gpa_he_10` float DEFAULT NULL,
  `gpa_he_4` float DEFAULT NULL,
  `gpa_tich_luy_he_10` float DEFAULT NULL,
  `gpa_tich_luy_he_4` float DEFAULT NULL,
  `tong_tin_chi` int DEFAULT NULL,
  `xep_loai` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `muc_canh_bao` tinyint DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `khoa`
--

CREATE TABLE `khoa` (
  `khoa_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ma_khoa` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_khoa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lich_su_hoc_mon`
--

CREATE TABLE `lich_su_hoc_mon` (
  `lich_su_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sinh_vien_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mon_hoc_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ds_lhp_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lan_hoc` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lop`
--

CREATE TABLE `lop` (
  `lop_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `khoa_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nien_khoa_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_lop` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lop_hoc_phan`
--

CREATE TABLE `lop_hoc_phan` (
  `lhp_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ma_lhp` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `giang_vien_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mon_hoc_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hoc_ky_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ty_le_cc` tinyint NOT NULL DEFAULT '10',
  `ty_le_gk` tinyint NOT NULL DEFAULT '30',
  `ty_le_ck` tinyint NOT NULL DEFAULT '60',
  `trang_thai_giao_vu` tinyint DEFAULT '1',
  `trang_thai_giang_vien` tinyint DEFAULT '0'
) ;

-- --------------------------------------------------------

--
-- Table structure for table `mon_hoc`
--

CREATE TABLE `mon_hoc` (
  `mon_hoc_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ma_mon` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_mon` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `so_tin_chi` int NOT NULL,
  `khoa_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_tinh_gpa` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nien_khoa`
--

CREATE TABLE `nien_khoa` (
  `nien_khoa_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ma_nien_khoa` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_nien_khoa` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quy_tac_quy_doi`
--

CREATE TABLE `quy_tac_quy_doi` (
  `quy_tac_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `diem_tu` float NOT NULL,
  `diem_den` float NOT NULL,
  `diem_chu` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `diem_he_4` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sinh_vien`
--

CREATE TABLE `sinh_vien` (
  `sinh_vien_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tai_khoan_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `msv` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_sv` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gioi_tinh` tinyint DEFAULT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `lop_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tai_khoan`
--

CREATE TABLE `tai_khoan` (
  `tai_khoan_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mat_khau` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vai_tro_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vai_tro`
--

CREATE TABLE `vai_tro` (
  `vai_tro_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ten_vai_tro` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mo_ta` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `yeu_cau_sua_diem`
--

CREATE TABLE `yeu_cau_sua_diem` (
  `yeu_cau_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lhp_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `giang_vien_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `giao_vu_duyet_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ly_do` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trang_thai_yc` tinyint DEFAULT '0',
  `thoi_gian_xin` datetime DEFAULT CURRENT_TIMESTAMP,
  `thoi_gian_xu_ly` datetime DEFAULT NULL,
  `ghi_chu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_diem`
--
ALTER TABLE `audit_diem`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `nguoi_thay_doi_id` (`nguoi_thay_doi_id`),
  ADD KEY `idx_audit_ds_lhp` (`ds_lhp_id`),
  ADD KEY `idx_audit_thoigian` (`thoi_diem`);

--
-- Indexes for table `ds_lhp`
--
ALTER TABLE `ds_lhp`
  ADD PRIMARY KEY (`ds_lhp_id`),
  ADD UNIQUE KEY `composite_lhp_sv` (`lhp_id`,`sinh_vien_id`),
  ADD KEY `idx_ds_lhp_sv` (`sinh_vien_id`),
  ADD KEY `idx_ds_lhp_lhp` (`lhp_id`);

--
-- Indexes for table `giang_vien`
--
ALTER TABLE `giang_vien`
  ADD PRIMARY KEY (`giang_vien_id`),
  ADD UNIQUE KEY `tai_khoan_id` (`tai_khoan_id`),
  ADD UNIQUE KEY `ma_gv` (`ma_gv`),
  ADD KEY `khoa_id` (`khoa_id`);

--
-- Indexes for table `giao_vu`
--
ALTER TABLE `giao_vu`
  ADD PRIMARY KEY (`giao_vu_id`),
  ADD UNIQUE KEY `tai_khoan_id` (`tai_khoan_id`),
  ADD UNIQUE KEY `ma_giao_vu` (`ma_giao_vu`);

--
-- Indexes for table `hoc_ky`
--
ALTER TABLE `hoc_ky`
  ADD PRIMARY KEY (`hoc_ky_id`),
  ADD UNIQUE KEY `ma_hoc_ky` (`ma_hoc_ky`);

--
-- Indexes for table `ket_qua_hoc_ky`
--
ALTER TABLE `ket_qua_hoc_ky`
  ADD PRIMARY KEY (`kqhk_id`),
  ADD UNIQUE KEY `uq_kqhk` (`sinh_vien_id`,`hoc_ky_id`),
  ADD KEY `hoc_ky_id` (`hoc_ky_id`),
  ADD KEY `idx_kqhk_sv` (`sinh_vien_id`);

--
-- Indexes for table `khoa`
--
ALTER TABLE `khoa`
  ADD PRIMARY KEY (`khoa_id`),
  ADD UNIQUE KEY `ma_khoa` (`ma_khoa`);

--
-- Indexes for table `lich_su_hoc_mon`
--
ALTER TABLE `lich_su_hoc_mon`
  ADD PRIMARY KEY (`lich_su_id`),
  ADD UNIQUE KEY `uq_lshm` (`sinh_vien_id`,`mon_hoc_id`,`lan_hoc`),
  ADD KEY `mon_hoc_id` (`mon_hoc_id`),
  ADD KEY `ds_lhp_id` (`ds_lhp_id`),
  ADD KEY `idx_lshm_sv_mon` (`sinh_vien_id`,`mon_hoc_id`);

--
-- Indexes for table `lop`
--
ALTER TABLE `lop`
  ADD PRIMARY KEY (`lop_id`),
  ADD KEY `khoa_id` (`khoa_id`),
  ADD KEY `nien_khoa_id` (`nien_khoa_id`);

--
-- Indexes for table `lop_hoc_phan`
--
ALTER TABLE `lop_hoc_phan`
  ADD PRIMARY KEY (`lhp_id`),
  ADD UNIQUE KEY `ma_lhp` (`ma_lhp`),
  ADD KEY `mon_hoc_id` (`mon_hoc_id`),
  ADD KEY `idx_lhp_hocky` (`hoc_ky_id`),
  ADD KEY `idx_lhp_gv` (`giang_vien_id`);

--
-- Indexes for table `mon_hoc`
--
ALTER TABLE `mon_hoc`
  ADD PRIMARY KEY (`mon_hoc_id`),
  ADD UNIQUE KEY `ma_mon` (`ma_mon`),
  ADD KEY `khoa_id` (`khoa_id`);

--
-- Indexes for table `nien_khoa`
--
ALTER TABLE `nien_khoa`
  ADD PRIMARY KEY (`nien_khoa_id`),
  ADD UNIQUE KEY `ma_nien_khoa` (`ma_nien_khoa`);

--
-- Indexes for table `quy_tac_quy_doi`
--
ALTER TABLE `quy_tac_quy_doi`
  ADD PRIMARY KEY (`quy_tac_id`);

--
-- Indexes for table `sinh_vien`
--
ALTER TABLE `sinh_vien`
  ADD PRIMARY KEY (`sinh_vien_id`),
  ADD UNIQUE KEY `tai_khoan_id` (`tai_khoan_id`),
  ADD UNIQUE KEY `msv` (`msv`),
  ADD KEY `lop_id` (`lop_id`);

--
-- Indexes for table `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD PRIMARY KEY (`tai_khoan_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `vai_tro_id` (`vai_tro_id`);

--
-- Indexes for table `vai_tro`
--
ALTER TABLE `vai_tro`
  ADD PRIMARY KEY (`vai_tro_id`);

--
-- Indexes for table `yeu_cau_sua_diem`
--
ALTER TABLE `yeu_cau_sua_diem`
  ADD PRIMARY KEY (`yeu_cau_id`),
  ADD KEY `lhp_id` (`lhp_id`),
  ADD KEY `giang_vien_id` (`giang_vien_id`),
  ADD KEY `giao_vu_duyet_id` (`giao_vu_duyet_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_diem`
--
ALTER TABLE `audit_diem`
  ADD CONSTRAINT `audit_diem_ibfk_1` FOREIGN KEY (`ds_lhp_id`) REFERENCES `ds_lhp` (`ds_lhp_id`),
  ADD CONSTRAINT `audit_diem_ibfk_2` FOREIGN KEY (`nguoi_thay_doi_id`) REFERENCES `tai_khoan` (`tai_khoan_id`);

--
-- Constraints for table `ds_lhp`
--
ALTER TABLE `ds_lhp`
  ADD CONSTRAINT `ds_lhp_ibfk_1` FOREIGN KEY (`lhp_id`) REFERENCES `lop_hoc_phan` (`lhp_id`),
  ADD CONSTRAINT `ds_lhp_ibfk_2` FOREIGN KEY (`sinh_vien_id`) REFERENCES `sinh_vien` (`sinh_vien_id`);

--
-- Constraints for table `giang_vien`
--
ALTER TABLE `giang_vien`
  ADD CONSTRAINT `giang_vien_ibfk_1` FOREIGN KEY (`tai_khoan_id`) REFERENCES `tai_khoan` (`tai_khoan_id`),
  ADD CONSTRAINT `giang_vien_ibfk_2` FOREIGN KEY (`khoa_id`) REFERENCES `khoa` (`khoa_id`);

--
-- Constraints for table `giao_vu`
--
ALTER TABLE `giao_vu`
  ADD CONSTRAINT `giao_vu_ibfk_1` FOREIGN KEY (`tai_khoan_id`) REFERENCES `tai_khoan` (`tai_khoan_id`);

--
-- Constraints for table `ket_qua_hoc_ky`
--
ALTER TABLE `ket_qua_hoc_ky`
  ADD CONSTRAINT `ket_qua_hoc_ky_ibfk_1` FOREIGN KEY (`sinh_vien_id`) REFERENCES `sinh_vien` (`sinh_vien_id`),
  ADD CONSTRAINT `ket_qua_hoc_ky_ibfk_2` FOREIGN KEY (`hoc_ky_id`) REFERENCES `hoc_ky` (`hoc_ky_id`);

--
-- Constraints for table `lich_su_hoc_mon`
--
ALTER TABLE `lich_su_hoc_mon`
  ADD CONSTRAINT `lich_su_hoc_mon_ibfk_1` FOREIGN KEY (`sinh_vien_id`) REFERENCES `sinh_vien` (`sinh_vien_id`),
  ADD CONSTRAINT `lich_su_hoc_mon_ibfk_2` FOREIGN KEY (`mon_hoc_id`) REFERENCES `mon_hoc` (`mon_hoc_id`),
  ADD CONSTRAINT `lich_su_hoc_mon_ibfk_3` FOREIGN KEY (`ds_lhp_id`) REFERENCES `ds_lhp` (`ds_lhp_id`);

--
-- Constraints for table `lop`
--
ALTER TABLE `lop`
  ADD CONSTRAINT `lop_ibfk_1` FOREIGN KEY (`khoa_id`) REFERENCES `khoa` (`khoa_id`),
  ADD CONSTRAINT `lop_ibfk_2` FOREIGN KEY (`nien_khoa_id`) REFERENCES `nien_khoa` (`nien_khoa_id`);

--
-- Constraints for table `lop_hoc_phan`
--
ALTER TABLE `lop_hoc_phan`
  ADD CONSTRAINT `lop_hoc_phan_ibfk_1` FOREIGN KEY (`giang_vien_id`) REFERENCES `giang_vien` (`giang_vien_id`),
  ADD CONSTRAINT `lop_hoc_phan_ibfk_2` FOREIGN KEY (`mon_hoc_id`) REFERENCES `mon_hoc` (`mon_hoc_id`),
  ADD CONSTRAINT `lop_hoc_phan_ibfk_3` FOREIGN KEY (`hoc_ky_id`) REFERENCES `hoc_ky` (`hoc_ky_id`);

--
-- Constraints for table `mon_hoc`
--
ALTER TABLE `mon_hoc`
  ADD CONSTRAINT `mon_hoc_ibfk_1` FOREIGN KEY (`khoa_id`) REFERENCES `khoa` (`khoa_id`);

--
-- Constraints for table `sinh_vien`
--
ALTER TABLE `sinh_vien`
  ADD CONSTRAINT `sinh_vien_ibfk_1` FOREIGN KEY (`tai_khoan_id`) REFERENCES `tai_khoan` (`tai_khoan_id`),
  ADD CONSTRAINT `sinh_vien_ibfk_2` FOREIGN KEY (`lop_id`) REFERENCES `lop` (`lop_id`);

--
-- Constraints for table `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD CONSTRAINT `tai_khoan_ibfk_1` FOREIGN KEY (`vai_tro_id`) REFERENCES `vai_tro` (`vai_tro_id`);

--
-- Constraints for table `yeu_cau_sua_diem`
--
ALTER TABLE `yeu_cau_sua_diem`
  ADD CONSTRAINT `yeu_cau_sua_diem_ibfk_1` FOREIGN KEY (`lhp_id`) REFERENCES `lop_hoc_phan` (`lhp_id`),
  ADD CONSTRAINT `yeu_cau_sua_diem_ibfk_2` FOREIGN KEY (`giang_vien_id`) REFERENCES `giang_vien` (`giang_vien_id`),
  ADD CONSTRAINT `yeu_cau_sua_diem_ibfk_3` FOREIGN KEY (`giao_vu_duyet_id`) REFERENCES `giao_vu` (`giao_vu_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
