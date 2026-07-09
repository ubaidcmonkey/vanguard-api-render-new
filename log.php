<?php
declare(strict_types=1);

require_once __DIR__ . '/request_logger.php';

$requestLog = echo_log_request();

http_response_code(200);
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'remote_ip' => $requestLog['ip'],
    'message' => 'emulator request logged'
]);
