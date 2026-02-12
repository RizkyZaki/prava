<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Wallet Unauthorized</title>
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
                linear-gradient(rgba(255, 215, 0, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 215, 0, 0.03) 1px, transparent 1px);
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
            background: #ffd700;
            border-radius: 50%;
            opacity: 0;
            animation: particleFloat 6s infinite;
        }

        .particle:nth-child(1) { left: 12%; animation-delay: 0s; animation-duration: 7s; }
        .particle:nth-child(2) { left: 25%; animation-delay: 1s; animation-duration: 8s; }
        .particle:nth-child(3) { left: 35%; animation-delay: 0.5s; animation-duration: 6s; }
        .particle:nth-child(4) { left: 48%; animation-delay: 2s; animation-duration: 9s; }
        .particle:nth-child(5) { left: 55%; animation-delay: 1.5s; animation-duration: 5s; }
        .particle:nth-child(6) { left: 68%; animation-delay: 3s; animation-duration: 7s; }
        .particle:nth-child(7) { left: 75%; animation-delay: 0.8s; animation-duration: 8s; }
        .particle:nth-child(8) { left: 88%; animation-delay: 2.5s; animation-duration: 6s; }

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

        .lock-icon {
            position: relative;
            width: 80px; height: 80px;
            margin: 0 auto 1.5rem;
            animation: lockPulse 2s ease-in-out infinite;
        }

        .lock-icon svg {
            width: 80px; height: 80px;
            filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.5));
        }

        @keyframes lockPulse {
            0%, 100% { transform: scale(1); filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.3)); }
            50% { transform: scale(1.05); filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.6)); }
        }

        .error-code {
            font-family: 'Orbitron', sans-serif;
            font-size: 160px;
            font-weight: 900;
            color: #ffd700;
            text-shadow:
                0 0 10px #ffd700,
                0 0 20px #ffd700,
                0 0 40px #ffd700,
                0 0 80px rgba(255, 215, 0, 0.3);
            line-height: 1;
            margin-bottom: 0.5rem;
            position: relative;
        }

        .error-code::before,
        .error-code::after {
            content: '403';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
        }

        .error-code::before {
            color: #ff6b00;
            animation: glitch-1 3s infinite;
            clip-path: polygon(0 0, 100% 0, 100% 33%, 0 33%);
        }

        .error-code::after {
            color: #ff0040;
            animation: glitch-2 3s infinite;
            clip-path: polygon(0 66%, 100% 66%, 100% 100%, 0 100%);
        }

        @keyframes glitch-1 {
            0%, 90%, 100% { transform: translate(0); }
            92% { transform: translate(-3px, 2px); }
            94% { transform: translate(3px, -1px); }
        }

        @keyframes glitch-2 {
            0%, 90%, 100% { transform: translate(0); }
            93% { transform: translate(2px, 1px); }
            95% { transform: translate(-2px, -2px); }
        }

        .hash-display {
            font-size: 0.75rem;
            color: rgba(255, 215, 0, 0.4);
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
            background: rgba(255, 215, 0, 0.05);
            border: 1px solid rgba(255, 215, 0, 0.2);
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .terminal-line {
            color: rgba(255, 215, 0, 0.7);
            font-size: 0.85rem;
            margin: 0.3rem 0;
        }

        .terminal-line .prompt { color: #ffd700; }
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
            background: #ffd700;
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
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5), 0 0 40px rgba(255, 215, 0, 0.2);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: #ffd700;
            border: 1px solid rgba(255, 215, 0, 0.4);
        }

        .btn-secondary:hover {
            border-color: #ffd700;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.2);
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
        <div class="lock-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="#ffd700" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                <circle cx="12" cy="16" r="1"/>
            </svg>
        </div>

        <div class="error-code">403</div>

        <div class="hash-display">WALLET: 0x000...UNAUTHORIZED — INSUFFICIENT_PERMISSION_LEVEL</div>

        <h1 class="error-title">Wallet Unauthorized</h1>

        <div class="terminal-box">
            <div class="terminal-line"><span class="prompt">$</span> wallet.authenticate()</div>
            <div class="terminal-line"><span class="error">DENIED:</span> Insufficient permissions for this smart contract</div>
            <div class="terminal-line"><span class="warning">HINT:</span> Wallet does not have required role to access this resource</div>
        </div>

        <p class="error-message">
            Wallet Anda tidak memiliki izin untuk mengakses resource ini. Pastikan Anda memiliki role yang tepat atau hubungi administrator untuk mendapatkan akses.
        </p>

        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn btn-primary">Genesis Block</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Previous Block</a>
        </div>
    </div>
</body>
</html>
