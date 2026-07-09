<?php
declare(strict_types=1);

const ECHO_STORAGE_DIR = __DIR__ . '/data';
const ECHO_VISITOR_FILE = ECHO_STORAGE_DIR . '/visitors.json';
const ECHO_ACTIVE_WINDOW_SECONDS = 300;

function echo_visitor_ip(): string
{
    $candidates = [];

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        foreach (explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']) as $forwardedIp) {
            $candidates[] = trim($forwardedIp);
        }
    }

    if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $candidates[] = trim($_SERVER['HTTP_X_REAL_IP']);
    }

    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $candidates[] = trim($_SERVER['REMOTE_ADDR']);
    }

    foreach ($candidates as $candidate) {
        if (filter_var($candidate, FILTER_VALIDATE_IP)) {
            return $candidate;
        }
    }

    return 'unknown';
}

function echo_load_visitors(): array
{
    if (!is_file(ECHO_VISITOR_FILE)) {
        return [];
    }

    $json = file_get_contents(ECHO_VISITOR_FILE);
    if ($json === false || $json === '') {
        return [];
    }

    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function echo_save_visitors(array $visitors): void
{
    if (!is_dir(ECHO_STORAGE_DIR)) {
        mkdir(ECHO_STORAGE_DIR, 0755, true);
    }

    $handle = fopen(ECHO_VISITOR_FILE, 'c+');
    if ($handle === false) {
        return;
    }

    flock($handle, LOCK_EX);
    ftruncate($handle, 0);
    rewind($handle);
    fwrite($handle, json_encode($visitors, JSON_PRETTY_PRINT));
    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);
}

function echo_log_request(): array
{
    static $logged = null;

    if (is_array($logged)) {
        return $logged;
    }

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $now = time();
    $ip = echo_visitor_ip();
    $visitors = echo_load_visitors();

    if ($path !== '/health.php') {
        if (!isset($visitors[$ip]) || !is_array($visitors[$ip])) {
            $visitors[$ip] = [
                'first_seen' => $now,
                'last_seen' => $now,
                'hits' => 0,
            ];
        }

        $visitors[$ip]['last_seen'] = $now;
        $visitors[$ip]['hits'] = (int)($visitors[$ip]['hits'] ?? 0) + 1;
        $visitors[$ip]['last_path'] = $path;
        $visitors[$ip]['method'] = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $visitors[$ip]['user_agent'] = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 180);

        uasort($visitors, static fn(array $a, array $b): int => ($b['last_seen'] ?? 0) <=> ($a['last_seen'] ?? 0));
        echo_save_visitors($visitors);
    }

    $logged = [
        'ip' => $ip,
        'now' => $now,
        'visitors' => $visitors,
        'active' => array_filter(
            $visitors,
            static fn(array $visitor): bool => $now - (int)($visitor['last_seen'] ?? 0) <= ECHO_ACTIVE_WINDOW_SECONDS
        ),
    ];

    return $logged;
}

echo_log_request();
