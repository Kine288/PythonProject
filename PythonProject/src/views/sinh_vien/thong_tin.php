<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'SINH_VIEN') {
    header('Location: ../auth/login.php');
    exit;
}

header('Location: ../auth/thong_tin_ca_nhan.php');
exit;
