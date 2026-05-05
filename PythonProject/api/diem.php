<?php
require_once __DIR__ . '/../config/constants.php';

$action = $_GET['action'] ?? '';
$data = json_decode(file_get_contents('php://input'), true);

$endpoint_map = [
	'luu_nhap' => '/diem/luu-nhap',
	'gui_duyet' => '/diem/gui-duyet',
	'duyet' => '/diem/duyet',
];

if (!isset($endpoint_map[$action])) {
	http_response_code(400);
	header('Content-Type: application/json');
	echo json_encode(['error' => 'Action khong hop le']);
	exit;
}

$url = PYTHON_API_URL . $endpoint_map[$action];
$ch = curl_init($url);
curl_setopt_array($ch, [
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS => json_encode($data),
	CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

http_response_code($status ?: 200);
header('Content-Type: application/json');
echo $response;
