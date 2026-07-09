<?php
declare(strict_types=1);

require_once __DIR__ . '/request_logger.php';

$requestLog = echo_log_request();
$now = $requestLog['now'];
$ip = $requestLog['ip'];
$visitors = $requestLog['visitors'];
$activeVisitors = $requestLog['active'];

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function ago(int $timestamp, int $now): string
{
    $seconds = max(0, $now - $timestamp);
    if ($seconds < 60) {
        return $seconds . 's ago';
    }

    $minutes = intdiv($seconds, 60);
    if ($minutes < 60) {
        return $minutes . 'm ago';
    }

    return intdiv($minutes, 60) . 'h ago';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="20">
    <title>ECHO</title>
    <style>
        :root {
            --bg: #050505;
            --panel: #101010;
            --panel-2: #17130a;
            --gold: #f7c84b;
            --gold-soft: #9f7a1f;
            --text: #f6f1df;
            --muted: #a99f86;
            --line: rgba(247, 200, 75, 0.22);
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 50% -10%, rgba(247, 200, 75, 0.18), transparent 32rem),
                linear-gradient(135deg, rgba(247, 200, 75, 0.06) 0 1px, transparent 1px 24px),
                var(--bg);
        }

        main {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
            padding: 48px 0;
        }

        .hero {
            min-height: 40vh;
            display: grid;
            align-content: center;
            border-bottom: 1px solid var(--line);
        }

        .eyebrow {
            color: var(--gold);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        h1 {
            margin: 8px 0 10px;
            font-size: clamp(4.5rem, 14vw, 11rem);
            line-height: 0.9;
            letter-spacing: 0;
            text-shadow: 0 0 28px rgba(247, 200, 75, 0.34);
        }

        .subhead {
            max-width: 680px;
            margin: 0;
            color: var(--muted);
            font-size: clamp(1rem, 2vw, 1.25rem);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin: 28px 0;
        }

        .stat {
            padding: 18px;
            border: 1px solid var(--line);
            background: linear-gradient(180deg, rgba(247, 200, 75, 0.08), rgba(16, 16, 16, 0.88));
            border-radius: 8px;
        }

        .stat span {
            display: block;
            color: var(--muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .stat strong {
            display: block;
            margin-top: 8px;
            color: var(--gold);
            font-size: 2rem;
        }

        .panel {
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            background: rgba(12, 12, 12, 0.86);
            box-shadow: 0 24px 90px rgba(0, 0, 0, 0.35);
        }

        .panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px;
            background: linear-gradient(90deg, rgba(247, 200, 75, 0.16), rgba(247, 200, 75, 0.03));
            border-bottom: 1px solid var(--line);
        }

        .panel-header h2 {
            margin: 0;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .pulse {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--gold);
            box-shadow: 0 0 22px var(--gold);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px 18px;
            text-align: left;
            border-bottom: 1px solid rgba(247, 200, 75, 0.11);
        }

        th {
            color: var(--gold);
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.14em;
        }

        td {
            color: var(--text);
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .ip {
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", monospace;
            color: #fff3bd;
        }

        .muted {
            color: var(--muted);
        }

        .empty {
            padding: 26px 18px;
            color: var(--muted);
        }

        @media (max-width: 720px) {
            main {
                width: min(100% - 20px, 1120px);
                padding: 28px 0;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            th:nth-child(3),
            td:nth-child(3) {
                display: none;
            }
        }
    </style>
</head>
<body>
    <main>
        <section class="hero">
            <div class="eyebrow">live gateway monitor</div>
            <h1>ECHO</h1>
            <p class="subhead">Blacksite status board tracking active visitors and saved connections in real time.</p>
        </section>

        <section class="stats" aria-label="Visitor stats">
            <div class="stat">
                <span>active users</span>
                <strong><?= count($activeVisitors) ?></strong>
            </div>
            <div class="stat">
                <span>saved ips</span>
                <strong><?= count($visitors) ?></strong>
            </div>
            <div class="stat">
                <span>your ip</span>
                <strong><?= h($ip) ?></strong>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <h2>Active Users</h2>
                <span class="pulse" aria-hidden="true"></span>
            </div>

            <?php if (count($activeVisitors) === 0): ?>
                <div class="empty">No active users detected.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Last Seen</th>
                            <th>Endpoint</th>
                            <th>Hits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeVisitors as $activeIp => $visitor): ?>
                            <tr>
                                <td class="ip"><?= h((string)$activeIp) ?></td>
                                <td class="muted"><?= h(ago((int)$visitor['last_seen'], $now)) ?></td>
                                <td class="muted"><?= h((string)($visitor['last_path'] ?? '/')) ?></td>
                                <td><?= (int)$visitor['hits'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
