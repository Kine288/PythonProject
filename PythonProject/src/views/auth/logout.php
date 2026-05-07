<?php
session_start();
require_once __DIR__ . '/../../../config/constants.php';
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
$baseUrl = rtrim(APP_BASE_URL, '/');
header('Location: ' . $baseUrl . '/src/views/auth/login.php');
exit;
