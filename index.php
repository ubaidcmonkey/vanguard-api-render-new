<?php
http_response_code(200);
header('Content-Type: application/json');

echo json_encode([
    'status' => 'ok',
    'service' => 'vanguard-api-render-new',
    'files' => [
        'Vanguard-Emulator.slnx',
        'Vanguard-Emulator/Vanguard-Emulator.cpp'
    ]
]);
