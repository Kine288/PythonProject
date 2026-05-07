<?php
require_once __DIR__ . '/../config/constants.php';

$action = $_REQUEST['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

function baoCaoProxyRequest(string $method, string $path, ?array $payload = null, array $query = []): array
{
    $url = rtrim(PYTHON_API_URL, '/') . '/api/bao-cao' . $path;
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

    $headers = ['Content-Type: application/json'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

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
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: '';
    curl_close($ch);

    if (stripos($contentType, 'application/pdf') !== false || stripos($contentType, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') !== false) {
        return [
            'success' => true,
            'status' => $statusCode,
            'is_file' => true,
            'raw' => $raw,
            'content_type' => $contentType,
        ];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return ['success' => false, 'message' => 'Python API tra ve du lieu khong hop le', 'status' => $statusCode ?: 500];
    }

    $decoded['status'] = $statusCode;
    return $decoded;
}

switch ($action) {
    case 'canh_bao':
        $hocKyId = $_GET['hoc_ky_id'] ?? ($input['hoc_ky_id'] ?? '');
        $muc = $_GET['muc_canh_bao'] ?? ($input['muc_canh_bao'] ?? '');
        $query = ['hoc_ky_id' => $hocKyId];
        if ($muc !== '') {
            $query['muc_canh_bao'] = $muc;
        }
        $response = baoCaoProxyRequest('GET', '/canh-bao', null, $query);
        break;

    case 'thong_ke_xep_loai':
        $hocKyId = $_GET['hoc_ky_id'] ?? ($input['hoc_ky_id'] ?? '');
        $response = baoCaoProxyRequest('GET', '/thong-ke-xep-loai', null, ['hoc_ky_id' => $hocKyId]);
        break;

    case 'xuat_excel_canh_bao':
        $response = baoCaoProxyRequest('POST', '/export/canh-bao-excel', [
            'hoc_ky_id' => $input['hoc_ky_id'] ?? '',
            'muc_canh_bao' => $input['muc_canh_bao'] ?? null,
        ]);
        break;

    case 'xuat_excel_tong_ket':
        $hocKyId = $_GET['hoc_ky_id'] ?? ($input['hoc_ky_id'] ?? '');
        $lopId = $_GET['lop_id'] ?? ($input['lop_id'] ?? '');
        $query = ['hoc_ky_id' => $hocKyId];
        if ($lopId !== '') {
            $query['lop_id'] = $lopId;
        }
        $response = baoCaoProxyRequest('GET', '/tong-ket-excel/' . $hocKyId, null, $query);
        break;

    case 'xuat_pdf_bang_diem':
        $response = baoCaoProxyRequest('POST', '/export/bang-diem-pdf', [
            'sinh_vien_id' => $input['sinh_vien_id'] ?? '',
        ]);
        break;

    default:
        $response = ['success' => false, 'message' => 'Action khong hop le', 'status' => 400];
        break;
}

if (!empty($response['is_file'])) {
    http_response_code($response['status'] ?? 200);
    header('Content-Type: ' . ($response['content_type'] ?? 'application/octet-stream'));

    if (stripos((string)$response['content_type'], 'application/pdf') !== false) {
        header('Content-Disposition: attachment; filename="bang_diem.pdf"');
    } else {
        header('Content-Disposition: attachment; filename="canh_bao.xlsx"');
    }

    echo $response['raw'];
    exit;
}

http_response_code($response['status'] ?? (!empty($response['success']) ? 200 : 400));
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
