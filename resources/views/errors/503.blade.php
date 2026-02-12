<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 — Maintenance</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #fafafa;
            color: #111;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .page {
            text-align: center;
            padding: 2rem;
            max-width: 460px;
        }
        .code {
            font-size: 7rem;
            font-weight: 600;
            letter-spacing: -4px;
            color: #111;
            line-height: 1;
        }
        .divider {
            width: 40px;
            height: 3px;
            background: #111;
            margin: 1.5rem auto;
            border-radius: 2px;
        }
        h1 {
            font-size: 1.15rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
            letter-spacing: -0.01em;
        }
        p {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
        }
        a {
            font-family: inherit;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        .btn-dark {
            background: #111;
            color: #fff;
        }
        .btn-dark:hover {
            background: #333;
        }
        .btn-light {
            background: transparent;
            color: #111;
            border: 1px solid #ddd;
        }
        .btn-light:hover {
            border-color: #999;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="code">503</div>
        <div class="divider"></div>
        <h1>Sedang Dalam Pemeliharaan</h1>
        <p>Sistem sedang dalam pemeliharaan terjadwal. Kami akan kembali secepatnya. Terima kasih atas kesabaran Anda.</p>
        <div class="actions">
            <a href="javascript:location.reload()" class="btn-dark">Coba Lagi</a>
        </div>
    </div>
</body>
</html>
