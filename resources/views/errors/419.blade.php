<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Token Expired</title>
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

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image:
                linear-gradient(rgba(0, 212, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 212, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 0;
        }

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
            background: #00d4ff;
            border-radius: 50%;
            opacity: 0;
            animation: particleFloat 6s infinite;
        }

        .particle:nth-child(1) { left: 8%; animation-delay: 0.2s; animation-duration: 7s; }
        .particle:nth-child(2) { left: 22%; animation-delay: 1.2s; animation-duration: 5s; }
        .particle:nth-child(3) { left: 33%; animation-delay: 0.7s; animation-duration: 8s; }
        .particle:nth-child(4) { left: 45%; animation-delay: 2.3s; animation-duration: 6s; }
        .particle:nth-child(5) { left: 58%; animation-delay: 1.8s; animation-duration: 9s; }
        .particle:nth-child(6) { left: 65%; animation-delay: 0.4s; animation-duration: 7s; }
        .particle:nth-child(7) { left: 78%; animation-delay: 3.1s; animation-duration: 5s; }
        .particle:nth-child(8) { left: 92%; animation-delay: 1.6s; animation-duration: 8s; }

        @keyframes particleFloat {
            0% { transform: translateY(100vh); opacity: 0; }
            10% { opacity: 0.5; }
            90% { opacity: 0.5; }
            100% { transform: translateY(-10vh); opacity: 0; }
        }

        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 700px;
            z-index: 2;
            position: relative;
        }

        /* Countdown ring */
        .timer-ring {
            width: 90px; height: 90px;
            margin: 0 auto 1.5rem;
            position: relative;
        }

        .timer-ring svg {
            width: 90px; height: 90px;
            transform: rotate(-90deg);
        }

        .timer-ring circle {
            fill: none;
            stroke-width: 3;
        }

        .timer-bg { stroke: rgba(0, 212, 255, 0.1); }

        .timer-progress {
            stroke: #00d4ff;
            stroke-dasharray: 251;
            stroke-dashoffset: 251;
            filter: drop-shadow(0 0 6px rgba(0, 212, 255, 0.6));
            animation: countdown 3s linear infinite;
        }

        @keyframes countdown {
            0% { stroke-dashoffset: 0; }
            100% { stroke-dashoffset: 251; }
        }

        .timer-text {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            font-family: 'Orbitron', sans-serif;
            font-size: 0.9rem;
            color: #00d4ff;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
        }

        .error-code {
            font-family: 'Orbitron', sans-serif;
            font-size: 160px;
            font-weight: 900;
            color: #00d4ff;
            text-shadow:
                0 0 10px #00d4ff,
                0 0 20px #00d4ff,
                0 0 40px #00d4ff,
                0 0 80px rgba(0, 212, 255, 0.3);
            line-height: 1;
            margin-bottom: 0.5rem;
            position: relative;
        }

        .error-code::before,
        .error-code::after {
            content: '419';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
        }

        .error-code::before {
            color: #a855f7;
            animation: glitch-1 4s infinite;
            clip-path: polygon(0 0, 100% 0, 100% 30%, 0 30%);
        }

        .error-code::after {
            color: #00ffa3;
            animation: glitch-2 4s infinite;
            clip-path: polygon(0 70%, 100% 70%, 100% 100%, 0 100%);
        }

        @keyframes glitch-1 {
            0%, 85%, 100% { transform: translate(0); }
            87% { transform: translate(-2px, 1px); }
            89% { transform: translate(2px, -1px); }
        }

        @keyframes glitch-2 {
            0%, 85%, 100% { transform: translate(0); }
            88% { transform: translate(1px, 2px); }
            90% { transform: translate(-1px, -1px); }
        }

        .hash-display {
            font-size: 0.75rem;
            color: rgba(0, 212, 255, 0.4);
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
            background: rgba(0, 212, 255, 0.05);
            border: 1px solid rgba(0, 212, 255, 0.2);
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .terminal-line {
            color: rgba(0, 212, 255, 0.7);
            font-size: 0.85rem;
            margin: 0.3rem 0;
        }

        .terminal-line .prompt { color: #00d4ff; }
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
            background: #00d4ff;
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
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.5), 0 0 40px rgba(0, 212, 255, 0.2);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: #00d4ff;
            border: 1px solid rgba(0, 212, 255, 0.4);
        }

        .btn-secondary:hover {
            border-color: #00d4ff;
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.2);
            transform: translateY(-2px);
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
        <div class="particle"></div><div class="particle"></div>
    </div>

    <div class="error-container">
        <div class="timer-ring">
            <svg viewBox="0 0 90 90">
                <circle class="timer-bg" cx="45" cy="45" r="40"/>
                <circle class="timer-progress" cx="45" cy="45" r="40"/>
            </svg>
            <div class="timer-text">EXPIRED</div>
        </div>

        <div class="error-code">419</div>

        <div class="hash-display">SESSION: 0xCSRF_TOKEN_EXPIRED — NONCE_VALIDATION_FAILED</div>

        <h1 class="error-title">Token Expired</h1>

        <div class="terminal-box">
            <div class="terminal-line"><span class="prompt">$</span> token.validate(csrf_nonce)</div>
            <div class="terminal-line"><span class="error">EXPIRED:</span> Session token has reached its TTL</div>
            <div class="terminal-line"><span class="warning">ACTION:</span> Refresh page to mint a new session token</div>
        </div>

        <p class="error-message">
            Token sesi Anda telah kadaluarsa. Untuk keamanan, sistem memerlukan token baru. Silakan muat ulang halaman untuk mendapatkan token sesi yang baru.
        </p>

        <div class="error-actions">
            <a href="javascript:location.reload()" class="btn btn-primary">Mint New Token</a>
            <a href="{{ url('/') }}" class="btn btn-secondary">Genesis Block</a>
        </div>
    </div>
</body>
</html>
