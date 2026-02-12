<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Smart Contract Failed</title>
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
                linear-gradient(rgba(255, 0, 64, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 0, 64, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 0;
        }

        /* Glitch scanlines */
        body::after {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(255, 0, 64, 0.01) 2px,
                rgba(255, 0, 64, 0.01) 4px
            );
            z-index: 1;
            pointer-events: none;
            animation: scanline 8s linear infinite;
        }

        @keyframes scanline {
            0% { transform: translateY(0); }
            100% { transform: translateY(4px); }
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
            width: 3px; height: 3px;
            background: #ff0040;
            border-radius: 50%;
            opacity: 0;
            animation: particleFall 5s infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 0.5s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 1s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 1.5s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 2s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 2.5s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 3s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 3.5s; }

        @keyframes particleFall {
            0% { transform: translateY(-10vh); opacity: 0; }
            10% { opacity: 0.8; }
            90% { opacity: 0.3; }
            100% { transform: translateY(100vh); opacity: 0; }
        }

        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 700px;
            z-index: 2;
            position: relative;
        }

        .error-code {
            font-family: 'Orbitron', sans-serif;
            font-size: 160px;
            font-weight: 900;
            color: #ff0040;
            text-shadow:
                0 0 10px #ff0040,
                0 0 20px #ff0040,
                0 0 40px #ff0040,
                0 0 80px rgba(255, 0, 64, 0.4);
            line-height: 1;
            margin-bottom: 0.5rem;
            position: relative;
            animation: critical 1s infinite;
        }

        .error-code::before,
        .error-code::after {
            content: '500';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
        }

        .error-code::before {
            color: #00ffa3;
            animation: glitch-1 0.8s infinite;
            clip-path: polygon(0 0, 100% 0, 100% 40%, 0 40%);
        }

        .error-code::after {
            color: #00d4ff;
            animation: glitch-2 0.8s infinite;
            clip-path: polygon(0 60%, 100% 60%, 100% 100%, 0 100%);
        }

        @keyframes critical {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.95; }
        }

        @keyframes glitch-1 {
            0%, 100% { transform: translate(0); }
            25% { transform: translate(-4px, 2px); }
            50% { transform: translate(4px, -2px); }
            75% { transform: translate(-2px, 4px); }
        }

        @keyframes glitch-2 {
            0%, 100% { transform: translate(0); }
            25% { transform: translate(4px, -2px); }
            50% { transform: translate(-4px, 2px); }
            75% { transform: translate(2px, -4px); }
        }

        .hash-display {
            font-size: 0.75rem;
            color: rgba(255, 0, 64, 0.4);
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
            background: rgba(255, 0, 64, 0.05);
            border: 1px solid rgba(255, 0, 64, 0.2);
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .terminal-line {
            color: rgba(255, 0, 64, 0.7);
            font-size: 0.85rem;
            margin: 0.3rem 0;
        }

        .terminal-line .prompt { color: #ff0040; }
        .terminal-line .error { color: #ff0040; font-weight: bold; }
        .terminal-line .info { color: #ffd700; }

        .status-bar {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .status-item {
            text-align: center;
        }

        .status-label {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 0.3rem;
        }

        .status-value {
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            color: #ff0040;
            text-shadow: 0 0 10px rgba(255, 0, 64, 0.4);
        }

        .status-value.ok {
            color: #00ffa3;
            text-shadow: 0 0 10px rgba(0, 255, 163, 0.4);
        }

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
            background: #ff0040;
            color: #fff;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-primary:hover::before { left: 100%; }
        .btn-primary:hover {
            box-shadow: 0 0 20px rgba(255, 0, 64, 0.5), 0 0 40px rgba(255, 0, 64, 0.2);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: #ff0040;
            border: 1px solid rgba(255, 0, 64, 0.4);
        }

        .btn-secondary:hover {
            border-color: #ff0040;
            box-shadow: 0 0 15px rgba(255, 0, 64, 0.2);
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
        <div class="status-bar">
            <div class="status-item">
                <div class="status-label">Network</div>
                <div class="status-value ok">Online</div>
            </div>
            <div class="status-item">
                <div class="status-label">Contract</div>
                <div class="status-value">Failed</div>
            </div>
            <div class="status-item">
                <div class="status-label">Gas</div>
                <div class="status-value">Reverted</div>
            </div>
        </div>

        <div class="error-code">500</div>

        <div class="hash-display">TX: 0xERR0R...SMART_CONTRACT_EXECUTION_REVERTED_INTERNAL_SERVER_FAILURE</div>

        <h1 class="error-title">Smart Contract Failed</h1>

        <div class="terminal-box">
            <div class="terminal-line"><span class="prompt">></span> Executing smart contract...</div>
            <div class="terminal-line"><span class="error">REVERT:</span> Execution failed with internal error</div>
            <div class="terminal-line"><span class="error">PANIC:</span> Server encountered unexpected state</div>
            <div class="terminal-line"><span class="info">INFO:</span> Transaction rolled back. Please retry.</div>
        </div>

        <p class="error-message">
            Smart contract mengalami kegagalan eksekusi. Server mengalami error internal yang tidak terduga. Tim kami sudah diberitahu dan sedang memperbaiki masalah ini.
        </p>

        <div class="error-actions">
            <a href="javascript:location.reload()" class="btn btn-primary">Retry Transaction</a>
            <a href="{{ url('/') }}" class="btn btn-secondary">Genesis Block</a>
        </div>
    </div>
</body>
</html>
