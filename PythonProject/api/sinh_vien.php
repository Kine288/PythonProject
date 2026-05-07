<?php

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

function sinhVienProxyRequest(string $method, string $path, ?array $payload = null, array $query = []): array
{
    $baseUrl = rtrim(PYTHON_API_URL, '/') . '/api';
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

    switch ($action) {
        case 'list_students':
            sinhVienProxyRespond(sinhVienProxyRequest('GET', '/sinh-vien', null, [
                'search' => $_GET['keyword'] ?? '',
                'lop_id' => $_GET['lop_id'] ?? '',
                'trang_thai' => $_GET['trang_thai'] ?? '',
            ]));
            break;

        case 'get_student':
            sinhVienProxyRespond(sinhVienProxyRequest('GET', '/sinh-vien/' . ($_GET['id'] ?? '')));
            break;

        case 'create_student':
            sinhVienProxyRespond(sinhVienProxyRequest('POST', '/sinh-vien', $input));
            break;

        case 'update_student':
            sinhVienProxyRespond(sinhVienProxyRequest('PUT', '/sinh-vien/' . ($input['sinh_vien_id'] ?? ''), $input));
            break;

        case 'get_transcript':
            sinhVienProxyRespond(sinhVienProxyRequest('GET', '/sinh-vien/' . ($_GET['id'] ?? '') . '/bang-diem', null, [
                'hoc_ky_id' => $_GET['hoc_ky_id'] ?? '',
            ]));
            break;

        case 'list_accounts':
            sinhVienProxyRespond(sinhVienProxyRequest('GET', '/tai-khoan', null, [
                'search' => $_GET['keyword'] ?? '',
                'vai_tro' => $_GET['vai_tro'] ?? '',
                'is_active' => $_GET['is_active'] ?? '',
            ]));
            break;

        case 'lock_account':
            sinhVienProxyRespond(sinhVienProxyRequest('PUT', '/tai-khoan/' . ($input['tai_khoan_id'] ?? '') . '/khoa', $input));
            break;

        case 'unlock_account':
            sinhVienProxyRespond(sinhVienProxyRequest('PUT', '/tai-khoan/' . ($input['tai_khoan_id'] ?? '') . '/mo-khoa', $input));
            break;

        case 'reset_password':
            sinhVienProxyRespond(sinhVienProxyRequest('PUT', '/tai-khoan/' . ($input['tai_khoan_id'] ?? '') . '/reset-mat-khau', [
                'mat_khau_moi' => $input['mat_khau_moi'] ?? '123456',
            ]));
            break;

        case 'change_role':
            sinhVienProxyRespond(sinhVienProxyRequest('PUT', '/tai-khoan/' . ($input['tai_khoan_id'] ?? '') . '/vai-tro', [
                'vai_tro' => $input['vai_tro'] ?? '',
            ]));
            break;

        case 'list_lop':
            $pdo = getDatabaseConnection();
            if ($pdo === null) {
                sinhVienProxyRespond([
                    'success' => false,
                    'message' => 'Khong ket noi duoc CSDL',
                    'status' => 500,
                ]);
                break;
            }

            $stmt = $pdo->query('SELECT lop_id, ma_lop, ten_lop FROM lop_sinh_hoat ORDER BY ma_lop ASC');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sinhVienProxyRespond([
                'success' => true,
                'data' => $rows,
                'status' => 200,
            ]);
            break;

        case 'list_lhp':
            sinhVienProxyRespond(sinhVienProxyRequest('GET', '/lhp', null, [
                'hoc_ky_id' => $_GET['hoc_ky_id'] ?? '',
                'giang_vien_id' => $_GET['giang_vien_id'] ?? '',
            ]));
            break;

        case 'create_lhp':
            sinhVienProxyRespond(sinhVienProxyRequest('POST', '/lhp', $input));
            break;

        case 'update_lhp':
            sinhVienProxyRespond(sinhVienProxyRequest('PUT', '/lhp/' . ($input['lhp_id'] ?? ''), $input));
            break;

        case 'delete_lhp':
            sinhVienProxyRespond(sinhVienProxyRequest('DELETE', '/lhp/' . ($input['lhp_id'] ?? '')));
            break;

        case 'add_student_to_lhp':
            sinhVienProxyRespond(sinhVienProxyRequest(
                'POST',
                '/lhp/' . ($input['lhp_id'] ?? '') . '/sinh-vien',
                ['sinh_vien_id' => $input['sinh_vien_id'] ?? null]
            ));
            break;

        case 'remove_student_from_lhp':
            sinhVienProxyRespond(sinhVienProxyRequest(
                'DELETE',
                '/lhp/' . ($input['lhp_id'] ?? '') . '/sinh-vien/' . ($input['sinh_vien_id'] ?? '')
            ));
            break;

        case 'list_lhp_students':
            sinhVienProxyRespond(sinhVienProxyRequest('GET', '/lhp/' . ($_GET['lhp_id'] ?? '') . '/danh-sach-sv'));
            break;

        case 'mo_nhap_diem':
            sinhVienProxyRespond(sinhVienProxyRequest('PUT', '/lhp/' . ($input['lhp_id'] ?? '') . '/mo-nhap-diem', $input));
            break;

        case 'khoa_nhap_diem':
            sinhVienProxyRespond(sinhVienProxyRequest('PUT', '/lhp/' . ($input['lhp_id'] ?? '') . '/khoa-nhap-diem', $input));
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
