<?php

if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
	session_start();
}

require_once __DIR__ . '/../config/constants.php';

function sinhVienProxyRequest(string $method, string $path, ?array $payload = null, array $query = []): array
{
	$baseUrl = rtrim(PYTHON_API_URL, '/') . '/api/sinh-vien';
	$url = $baseUrl . $path;

	if (!empty($query)) {
		$url .= '?' . http_build_query($query);
	}

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	curl_setopt($ch, CURLOPT_TIMEOUT, 12);

	if ($payload !== null) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
	}

	$raw = curl_exec($ch);
	if ($raw === false) {
		$error = curl_error($ch);
		curl_close($ch);
		return [
			'success' => false,
			'message' => 'Khong the ket noi Python API: ' . $error,
		];
	}

	$statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	$decoded = json_decode($raw, true);
	if (!is_array($decoded)) {
		return [
			'success' => false,
			'message' => 'Python API tra ve du lieu khong hop le',
			'status' => $statusCode,
		];
	}

	$decoded['status'] = $statusCode;
	return $decoded;
}


function sinhVienProxyRespond(array $response): void
{
	header('Content-Type: application/json; charset=utf-8');
	http_response_code($response['status'] ?? ($response['success'] ? 200 : 400));
	echo json_encode($response, JSON_UNESCAPED_UNICODE);
}


if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
	$action = $_REQUEST['action'] ?? '';
	$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
	$input = json_decode(file_get_contents('php://input'), true);
	if (!is_array($input)) {
		$input = $_POST;
	}

	if ($action === '') {
		$action = 'list_students';
	}

	switch ($action) {
		case 'list_students':
			sinhVienProxyRespond(sinhVienProxyRequest('GET', '', null, [
				'keyword' => $_GET['keyword'] ?? '',
				'lop_id' => $_GET['lop_id'] ?? '',
			]));
			break;

		case 'get_student':
			sinhVienProxyRespond(sinhVienProxyRequest('GET', '/students/' . ($_GET['id'] ?? '')));
			break;

		case 'create_student':
			if (($_SESSION['user_role'] ?? '') !== 'ADMIN') {
				sinhVienProxyRespond([
					'success' => false,
					'message' => 'Chi ADMIN moi co quyen tao sinh vien',
					'status' => 403,
				]);
				break;
			}
			sinhVienProxyRespond(sinhVienProxyRequest('POST', '/students', $input));
			break;

		case 'update_student':
			sinhVienProxyRespond(sinhVienProxyRequest('PUT', '/students/' . ($input['sinh_vien_id'] ?? ''), $input));
			break;

		case 'delete_student':
			sinhVienProxyRespond(sinhVienProxyRequest('DELETE', '/students/' . ($input['sinh_vien_id'] ?? '')));
			break;

		case 'transfer_class':
			sinhVienProxyRespond(sinhVienProxyRequest('PUT', '/chuyen-lop', [
				'sinh_vien_id' => $input['sinh_vien_id'] ?? '',
				'lop_id_moi' => $input['lop_id_moi'] ?? null,
			]));
			break;

		case 'list_lop':
			sinhVienProxyRespond(sinhVienProxyRequest('GET', '/catalog/lop'));
			break;

		case 'list_giang_vien':
			sinhVienProxyRespond(sinhVienProxyRequest('GET', '/catalog/giang-vien'));
			break;

		case 'list_mon_hoc':
			sinhVienProxyRespond(sinhVienProxyRequest('GET', '/catalog/mon-hoc'));
			break;

		case 'list_hoc_ky':
			sinhVienProxyRespond(sinhVienProxyRequest('GET', '/catalog/hoc-ky'));
			break;

		case 'list_lhp':
			sinhVienProxyRespond(sinhVienProxyRequest('GET', '/lhp'));
			break;

		case 'create_lhp':
			sinhVienProxyRespond(sinhVienProxyRequest('POST', '/lhp', $input));
			break;

		case 'assign_lecturer':
			sinhVienProxyRespond(sinhVienProxyRequest(
				'PUT',
				'/lhp/' . ($input['lhp_id'] ?? '') . '/assign-lecturer',
				['giang_vien_id' => $input['giang_vien_id'] ?? null]
			));
			break;

		case 'add_student_to_lhp':
			sinhVienProxyRespond(sinhVienProxyRequest(
				'POST',
				'/lhp/' . ($input['lhp_id'] ?? '') . '/students',
				['sinh_vien_id' => $input['sinh_vien_id'] ?? null]
			));
			break;

		case 'remove_student_from_lhp':
			sinhVienProxyRespond(sinhVienProxyRequest(
				'DELETE',
				'/lhp/' . ($input['lhp_id'] ?? '') . '/students/' . ($input['sinh_vien_id'] ?? '')
			));
			break;

		case 'list_lhp_students':
			sinhVienProxyRespond(sinhVienProxyRequest('GET', '/lhp/' . ($_GET['lhp_id'] ?? '') . '/students'));
			break;

		default:
			sinhVienProxyRespond([
				'success' => false,
				'message' => 'Action khong hop le',
				'status' => 400,
			]);
			break;
	}
}
