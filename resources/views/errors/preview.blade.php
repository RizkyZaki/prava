<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Pages Preview</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Share Tech Mono', monospace;
            background: #0a0a0f;
            min-height: 100vh;
            padding: 3rem 1.5rem;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image:
                linear-gradient(rgba(0, 255, 163, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 163, 0.02) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 0;
            pointer-events: none;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            color: #00ffa3;
            text-transform: uppercase;
            letter-spacing: 6px;
            text-shadow: 0 0 20px rgba(0, 255, 163, 0.4);
            margin-bottom: 0.5rem;
        }

        .header p {
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.9rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent);
            transition: left 0.6s;
        }

        .card:hover::before { left: 100%; }

        .card:hover {
            transform: translateY(-4px);
        }

        .card-code {
            font-family: 'Orbitron', sans-serif;
            font-size: 3.5rem;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 0.8rem;
        }

        .card-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 0.8rem;
        }

        .card-desc {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.4);
            line-height: 1.5;
        }

        .card-btn {
            margin-top: 1.2rem;
            display: inline-block;
            padding: 8px 20px;
            border-radius: 4px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s ease;
        }

        /* 404 - Green */
        .card-404 { border-color: rgba(0, 255, 163, 0.15); }
        .card-404:hover { border-color: rgba(0, 255, 163, 0.4); box-shadow: 0 0 30px rgba(0, 255, 163, 0.1); }
        .card-404 .card-code { color: #00ffa3; text-shadow: 0 0 20px rgba(0, 255, 163, 0.4); }
        .card-404 .card-title { color: #00ffa3; }
        .card-404 .card-btn { background: rgba(0, 255, 163, 0.1); color: #00ffa3; border: 1px solid rgba(0, 255, 163, 0.3); }
        .card-404:hover .card-btn { background: #00ffa3; color: #0a0a0f; }

        /* 500 - Red */
        .card-500 { border-color: rgba(255, 0, 64, 0.15); }
        .card-500:hover { border-color: rgba(255, 0, 64, 0.4); box-shadow: 0 0 30px rgba(255, 0, 64, 0.1); }
        .card-500 .card-code { color: #ff0040; text-shadow: 0 0 20px rgba(255, 0, 64, 0.4); }
        .card-500 .card-title { color: #ff0040; }
        .card-500 .card-btn { background: rgba(255, 0, 64, 0.1); color: #ff0040; border: 1px solid rgba(255, 0, 64, 0.3); }
        .card-500:hover .card-btn { background: #ff0040; color: #fff; }

        /* 403 - Gold */
        .card-403 { border-color: rgba(255, 215, 0, 0.15); }
        .card-403:hover { border-color: rgba(255, 215, 0, 0.4); box-shadow: 0 0 30px rgba(255, 215, 0, 0.1); }
        .card-403 .card-code { color: #ffd700; text-shadow: 0 0 20px rgba(255, 215, 0, 0.4); }
        .card-403 .card-title { color: #ffd700; }
        .card-403 .card-btn { background: rgba(255, 215, 0, 0.1); color: #ffd700; border: 1px solid rgba(255, 215, 0, 0.3); }
        .card-403:hover .card-btn { background: #ffd700; color: #0a0a0f; }

        /* 419 - Cyan */
        .card-419 { border-color: rgba(0, 212, 255, 0.15); }
        .card-419:hover { border-color: rgba(0, 212, 255, 0.4); box-shadow: 0 0 30px rgba(0, 212, 255, 0.1); }
        .card-419 .card-code { color: #00d4ff; text-shadow: 0 0 20px rgba(0, 212, 255, 0.4); }
        .card-419 .card-title { color: #00d4ff; }
        .card-419 .card-btn { background: rgba(0, 212, 255, 0.1); color: #00d4ff; border: 1px solid rgba(0, 212, 255, 0.3); }
        .card-419:hover .card-btn { background: #00d4ff; color: #0a0a0f; }

        /* 503 - Purple */
        .card-503 { border-color: rgba(168, 85, 247, 0.15); }
        .card-503:hover { border-color: rgba(168, 85, 247, 0.4); box-shadow: 0 0 30px rgba(168, 85, 247, 0.1); }
        .card-503 .card-code { color: #a855f7; text-shadow: 0 0 20px rgba(168, 85, 247, 0.4); }
        .card-503 .card-title { color: #a855f7; }
        .card-503 .card-btn { background: rgba(168, 85, 247, 0.1); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.3); }
        .card-503:hover .card-btn { background: #a855f7; color: #fff; }

        .footer {
            text-align: center;
            margin-top: 3rem;
            color: rgba(255, 255, 255, 0.2);
            font-size: 0.75rem;
        }

        .footer a {
            color: #00ffa3;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Error Pages</h1>
            <p>// blockchain-themed error pages preview</p>
        </div>

        <div class="grid">
            <a href="{{ route('error.show', 403) }}" class="card card-403">
                <div class="card-code">403</div>
                <div class="card-title">Wallet Unauthorized</div>
                <p class="card-desc">Akses ditolak — wallet tidak memiliki izin</p>
                <span class="card-btn">Preview</span>
            </a>

            <a href="{{ route('error.show', 404) }}" class="card card-404">
                <div class="card-code">404</div>
                <div class="card-title">Block Not Found</div>
                <p class="card-desc">Block tidak ditemukan di dalam chain</p>
                <span class="card-btn">Preview</span>
            </a>

            <a href="{{ route('error.show', 419) }}" class="card card-419">
                <div class="card-code">419</div>
                <div class="card-title">Token Expired</div>
                <p class="card-desc">Token sesi kadaluarsa, perlu mint ulang</p>
                <span class="card-btn">Preview</span>
            </a>

            <a href="{{ route('error.show', 500) }}" class="card card-500">
                <div class="card-code">500</div>
                <div class="card-title">Smart Contract Failed</div>
                <p class="card-desc">Eksekusi smart contract mengalami revert</p>
                <span class="card-btn">Preview</span>
            </a>

            <a href="{{ route('error.show', 503) }}" class="card card-503">
                <div class="card-code">503</div>
                <div class="card-title">Node Maintenance</div>
                <p class="card-desc">Node sedang maintenance & sinkronisasi</p>
                <span class="card-btn">Preview</span>
            </a>
        </div>

        <div class="footer">
            <a href="{{ url('/') }}">← Back to Application</a>
        </div>
    </div>
</body>
</html>
