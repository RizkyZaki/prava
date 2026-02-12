<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Block Not Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;800;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Share Tech Mono', monospace;
            background: #0a0a0f;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated grid background */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image:
                linear-gradient(rgba(0, 255, 163, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 163, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 0;
        }

        /* Floating particles */
        .particles {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px; height: 4px;
            background: #00ffa3;
            border-radius: 50%;
            opacity: 0;
            animation: particleFloat 6s infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; animation-duration: 8s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 1s; animation-duration: 6s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 2s; animation-duration: 9s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 0.5s; animation-duration: 7s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 1.5s; animation-duration: 5s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 3s; animation-duration: 8s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 2.5s; animation-duration: 6s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 0.8s; animation-duration: 9s; }
        .particle:nth-child(9) { left: 90%; animation-delay: 1.2s; animation-duration: 7s; }
        .particle:nth-child(10) { left: 15%; animation-delay: 3.5s; animation-duration: 5s; }

        @keyframes particleFloat {
            0% { transform: translateY(100vh); opacity: 0; }
            10% { opacity: 0.6; }
            90% { opacity: 0.6; }
            100% { transform: translateY(-10vh); opacity: 0; }
        }

        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 700px;
            z-index: 2;
            position: relative;
        }

        .glitch-wrapper {
            position: relative;
            display: inline-block;
        }

        .error-code {
            font-family: 'Orbitron', sans-serif;
            font-size: 160px;
            font-weight: 900;
            color: #00ffa3;
            text-shadow:
                0 0 10px #00ffa3,
                0 0 20px #00ffa3,
                0 0 40px #00ffa3,
                0 0 80px rgba(0, 255, 163, 0.4);
            line-height: 1;
            margin-bottom: 0.5rem;
            position: relative;
        }

        .error-code::before,
        .error-code::after {
            content: '404';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
        }

        .error-code::before {
            color: #ff0040;
            animation: glitch-1 2s infinite;
            clip-path: polygon(0 0, 100% 0, 100% 35%, 0 35%);
            transform: translate(-2px);
        }

        .error-code::after {
            color: #00d4ff;
            animation: glitch-2 2s infinite;
            clip-path: polygon(0 65%, 100% 65%, 100% 100%, 0 100%);
            transform: translate(2px);
        }

        @keyframes glitch-1 {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(-3px, 3px); }
            40% { transform: translate(-3px, -3px); }
            60% { transform: translate(3px, 3px); }
            80% { transform: translate(3px, -3px); }
        }

        @keyframes glitch-2 {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(3px, -3px); }
            40% { transform: translate(3px, 3px); }
            60% { transform: translate(-3px, -3px); }
            80% { transform: translate(-3px, 3px); }
        }

        .hash-display {
            font-family: 'Share Tech Mono', monospace;
            font-size: 0.75rem;
            color: rgba(0, 255, 163, 0.4);
            margin-bottom: 1.5rem;
            word-break: break-all;
            letter-spacing: 1px;
        }

        .error-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        .error-message {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 2rem;
            line-height: 1.8;
        }

        .terminal-box {
            background: rgba(0, 255, 163, 0.05);
            border: 1px solid rgba(0, 255, 163, 0.2);
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .terminal-line {
            color: rgba(0, 255, 163, 0.7);
            font-size: 0.85rem;
            margin: 0.3rem 0;
        }

        .terminal-line .prompt { color: #00ffa3; }
        .terminal-line .error { color: #ff0040; }
        .terminal-line .warning { color: #ffd700; }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 4px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.3s ease;
            display: inline-block;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: #00ffa3;
            color: #0a0a0f;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before { left: 100%; }
        .btn-primary:hover {
            box-shadow: 0 0 20px rgba(0, 255, 163, 0.5), 0 0 40px rgba(0, 255, 163, 0.2);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: #00ffa3;
            border: 1px solid rgba(0, 255, 163, 0.4);
        }

        .btn-secondary:hover {
            border-color: #00ffa3;
            box-shadow: 0 0 15px rgba(0, 255, 163, 0.2);
            transform: translateY(-2px);
        }

        .chain-links {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-bottom: 2rem;
        }

        .chain-block {
            width: 40px; height: 40px;
            border: 1px solid rgba(0, 255, 163, 0.3);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: blockPulse 2s infinite;
            position: relative;
        }

        .chain-block::after {
            content: '';
            position: absolute;
            right: -7px;
            width: 7px; height: 2px;
            background: rgba(0, 255, 163, 0.3);
        }

        .chain-block:last-child::after { display: none; }

        .chain-block.broken {
            border-color: #ff0040;
            animation: brokenPulse 1s infinite;
        }

        .chain-block svg { width: 16px; height: 16px; }

        @keyframes blockPulse {
            0%, 100% { box-shadow: 0 0 5px rgba(0, 255, 163, 0.1); }
            50% { box-shadow: 0 0 15px rgba(0, 255, 163, 0.2); }
        }

        @keyframes brokenPulse {
            0%, 100% { box-shadow: 0 0 5px rgba(255, 0, 64, 0.2); }
            50% { box-shadow: 0 0 20px rgba(255, 0, 64, 0.4); }
        }

        @media (max-width: 600px) {
            .error-code { font-size: 100px; }
            .error-title { font-size: 1.2rem; letter-spacing: 2px; }
        }
    </style>
</head>
<body>
    <div class="particles">
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="error-container">
        <div class="chain-links">
            <div class="chain-block"><svg viewBox="0 0 24 24" fill="none" stroke="#00ffa3" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>
            <div class="chain-block"><svg viewBox="0 0 24 24" fill="none" stroke="#00ffa3" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>
            <div class="chain-block broken"><svg viewBox="0 0 24 24" fill="none" stroke="#ff0040" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
            <div class="chain-block" style="opacity:0.3"><svg viewBox="0 0 24 24" fill="none" stroke="#00ffa3" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" stroke-dasharray="4"/></svg></div>
        </div>

        <div class="glitch-wrapper">
            <div class="error-code">404</div>
        </div>

        <div class="hash-display">0x00000000000000000000000000000000000000000000000000000000DEADBLOCK</div>

        <h1 class="error-title">Block Not Found</h1>

        <div class="terminal-box">
            <div class="terminal-line"><span class="prompt">$</span> request.lookup(block: "{{ request()->path() }}")</div>
            <div class="terminal-line"><span class="error">ERROR:</span> Block not found in chain</div>
            <div class="terminal-line"><span class="warning">WARN:</span> The requested route does not exist on this ledger</div>
        </div>

        <p class="error-message">
            Transaksi gagal — block yang Anda cari tidak ditemukan di dalam chain. Periksa kembali alamat yang Anda tuju atau kembali ke genesis block.
        </p>

        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn btn-primary">Genesis Block</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Previous Block</a>
        </div>
    </div>
</body>
</html>
