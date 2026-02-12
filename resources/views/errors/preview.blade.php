<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Pages Preview</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0e0e0e;
            color: #e5e5e5;
            min-height: 100vh;
            padding: 4rem 1.5rem;
        }
        .container { max-width: 640px; margin: 0 auto; }
        .header { margin-bottom: 3rem; }
        .header h1 { font-size: 1.5rem; font-weight: 600; color: #fff; letter-spacing: -0.02em; }
        .header p { color: #555; font-size: 0.85rem; margin-top: 0.4rem; }
        .list {
            display: flex; flex-direction: column; gap: 1px;
            background: #1a1a1a; border-radius: 10px; overflow: hidden;
        }
        .item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.25rem 1.5rem; background: #141414;
            text-decoration: none; color: inherit; transition: background 0.15s ease;
        }
        .item:hover { background: #1a1a1a; }
        .item-left { display: flex; align-items: center; gap: 1.25rem; }
        .item-code { font-size: 1.5rem; font-weight: 600; width: 56px; color: #fff; letter-spacing: -1px; }
        .item-info h2 { font-size: 0.9rem; font-weight: 500; color: #ccc; }
        .item-info span { font-size: 0.78rem; color: #555; }
        .arrow { color: #333; font-size: 1.1rem; transition: all 0.15s ease; }
        .item:hover .arrow { transform: translateX(3px); color: #777; }
        .footer { margin-top: 2rem; text-align: center; }
        .footer a { font-size: 0.8rem; color: #555; text-decoration: none; }
        .footer a:hover { color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Error Pages</h1>
            <p>Preview semua custom error pages</p>
        </div>
        <div class="list">
            <a href="{{ route('error.show', 403) }}" class="item">
                <div class="item-left">
                    <div class="item-code">403</div>
                    <div class="item-info"><h2>Forbidden</h2><span>Akses ditolak</span></div>
                </div>
                <div class="arrow">→</div>
            </a>
            <a href="{{ route('error.show', 404) }}" class="item">
                <div class="item-left">
                    <div class="item-code">404</div>
                    <div class="item-info"><h2>Not Found</h2><span>Halaman tidak ditemukan</span></div>
                </div>
                <div class="arrow">→</div>
            </a>
            <a href="{{ route('error.show', 419) }}" class="item">
                <div class="item-left">
                    <div class="item-code">419</div>
                    <div class="item-info"><h2>Session Expired</h2><span>Sesi kedaluwarsa</span></div>
                </div>
                <div class="arrow">→</div>
            </a>
            <a href="{{ route('error.show', 500) }}" class="item">
                <div class="item-left">
                    <div class="item-code">500</div>
                    <div class="item-info"><h2>Server Error</h2><span>Kesalahan server internal</span></div>
                </div>
                <div class="arrow">→</div>
            </a>
            <a href="{{ route('error.show', 503) }}" class="item">
                <div class="item-left">
                    <div class="item-code">503</div>
                    <div class="item-info"><h2>Maintenance</h2><span>Sedang dalam pemeliharaan</span></div>
                </div>
                <div class="arrow">→</div>
            </a>
        </div>
        <div class="footer"><a href="{{ url('/') }}">← Kembali ke Aplikasi</a></div>
    </div>
</body>
</html>
