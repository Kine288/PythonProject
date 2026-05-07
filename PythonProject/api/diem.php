<?php
require_once __DIR__ . '/../config/constants.php';

$action = $_REQUEST['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
	$input = $_POST;
}

function diemProxyRequest(string $method, string $path, ?array $payload = null): array
{
	$url = rtrim(PYTHON_API_URL, '/') . '/api/diem' . $path;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

	if ($payload !== null) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	}

	$raw = curl_exec($ch);
	if ($raw === false) {
		$error = curl_error($ch);
		curl_close($ch);
		return ['success' => false, 'message' => 'Khong the ket noi Python API: ' . $error, 'status' => 502];
	}

	$statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	$decoded = json_decode($raw, true);
	if (!is_array($decoded)) {
		return ['success' => false, 'message' => 'Python API tra ve du lieu khong hop le', 'status' => $statusCode ?: 500];
	}

	$decoded['status'] = $statusCode;
	return $decoded;
}

switch ($action) {
	case 'lay_bang_diem_lhp':
		$lhpId = $_GET['lhp_id'] ?? ($input['lhp_id'] ?? '');
		$response = diemProxyRequest('GET', '/lhp/' . $lhpId);
		break;

	case 'luu_nhap':
		$lhpId = $input['lhp_id'] ?? ($_GET['lhp_id'] ?? '');
		$response = diemProxyRequest('PUT', '/lhp/' . $lhpId, [
			'tai_khoan_id' => $input['tai_khoan_id'] ?? '',
			'rows' => $input['rows'] ?? [],
			'ly_do' => $input['ly_do'] ?? '',
		]);
		break;

	case 'gui_duyet':
		$lhpId = $input['lhp_id'] ?? ($_GET['lhp_id'] ?? '');
		$response = diemProxyRequest('POST', '/lhp/' . $lhpId . '/gui-duyet', []);
		break;

	case 'duyet':
		// Python endpoint /duyet triggers GPA recalculation via Python service.
		$lhpId = $input['lhp_id'] ?? ($_GET['lhp_id'] ?? '');
		$response = diemProxyRequest('POST', '/lhp/' . $lhpId . '/duyet', [
			'tai_khoan_id' => $input['tai_khoan_id'] ?? '',
		]);
		break;

	case 'tu_choi':
		$lhpId = $input['lhp_id'] ?? ($_GET['lhp_id'] ?? '');
		$response = diemProxyRequest('POST', '/lhp/' . $lhpId . '/tu-choi', [
			'ly_do' => $input['ly_do'] ?? '',
		]);
		break;

	default:
		$response = ['success' => false, 'message' => 'Action khong hop le', 'status' => 400];
		break;
}

http_response_code($response['status'] ?? (!empty($response['success']) ? 200 : 400));
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
