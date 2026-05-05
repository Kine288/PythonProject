<?php
require_once __DIR__ . '/../config/constants.php';

$action = $_REQUEST['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
	$input = $_POST;
}

function gpaProxyRequest(string $method, string $path, ?array $payload = null, array $query = []): array
{
	$url = rtrim(PYTHON_API_URL, '/') . '/api/gpa' . $path;
	if (!empty($query)) {
		$url .= '?' . http_build_query($query);
	}

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
	case 'recalculate':
		$response = gpaProxyRequest('POST', '/recalculate', [
			'hoc_ky_id' => $input['hoc_ky_id'] ?? '',
			'sinh_vien_id' => $input['sinh_vien_id'] ?? '',
		]);
		break;

	case 'student_result':
		$sinhVienId = $_GET['sinh_vien_id'] ?? ($input['sinh_vien_id'] ?? '');
		$hocKyId = $_GET['hoc_ky_id'] ?? ($input['hoc_ky_id'] ?? '');
		$response = gpaProxyRequest('GET', '/students/' . $sinhVienId, null, ['hoc_ky_id' => $hocKyId]);
		break;

	case 'warnings':
		$hocKyId = $_GET['hoc_ky_id'] ?? ($input['hoc_ky_id'] ?? '');
		$response = gpaProxyRequest('GET', '/warnings', null, ['hoc_ky_id' => $hocKyId]);
		break;

	default:
		$response = ['success' => false, 'message' => 'Action khong hop le', 'status' => 400];
		break;
}

http_response_code($response['status'] ?? (!empty($response['success']) ? 200 : 400));
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
