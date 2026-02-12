<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Node Maintenance</title>
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
                linear-gradient(rgba(168, 85, 247, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(168, 85, 247, 0.03) 1px, transparent 1px);
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
            background: #a855f7;
            border-radius: 50%;
            opacity: 0;
            animation: particleFloat 7s infinite;
        }

        .particle:nth-child(1) { left: 5%; animation-delay: 0s; animation-duration: 9s; }
        .particle:nth-child(2) { left: 18%; animation-delay: 1.5s; animation-duration: 6s; }
        .particle:nth-child(3) { left: 32%; animation-delay: 0.3s; animation-duration: 8s; }
        .particle:nth-child(4) { left: 42%; animation-delay: 2.8s; animation-duration: 7s; }
        .particle:nth-child(5) { left: 55%; animation-delay: 0.9s; animation-duration: 5s; }
        .particle:nth-child(6) { left: 63%; animation-delay: 3.2s; animation-duration: 9s; }
        .particle:nth-child(7) { left: 77%; animation-delay: 1.1s; animation-duration: 6s; }
        .particle:nth-child(8) { left: 85%; animation-delay: 2.1s; animation-duration: 8s; }
        .particle:nth-child(9) { left: 93%; animation-delay: 0.6s; animation-duration: 7s; }
        .particle:nth-child(10) { left: 48%; animation-delay: 3.8s; animation-duration: 5s; }

        @keyframes particleFloat {
            0% { transform: translateY(100vh); opacity: 0; }
            10% { opacity: 0.5; }
            90% { opacity: 0.3; }
            100% { transform: translateY(-10vh); opacity: 0; }
        }

        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 700px;
            z-index: 2;
            position: relative;
        }

        /* Mining animation */
        .mining-animation {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 1.5rem;
            align-items: flex-end;
            height: 60px;
        }

        .mining-bar {
            width: 6px;
            background: #a855f7;
            border-radius: 3px;
            animation: mining 1.2s ease-in-out infinite;
            box-shadow: 0 0 10px rgba(168, 85, 247, 0.5);
        }

        .mining-bar:nth-child(1) { height: 20px; animation-delay: 0s; }
        .mining-bar:nth-child(2) { height: 35px; animation-delay: 0.1s; }
        .mining-bar:nth-child(3) { height: 25px; animation-delay: 0.2s; }
        .mining-bar:nth-child(4) { height: 45px; animation-delay: 0.3s; }
        .mining-bar:nth-child(5) { height: 15px; animation-delay: 0.4s; }
        .mining-bar:nth-child(6) { height: 40px; animation-delay: 0.5s; }
        .mining-bar:nth-child(7) { height: 30px; animation-delay: 0.6s; }
        .mining-bar:nth-child(8) { height: 50px; animation-delay: 0.7s; }
        .mining-bar:nth-child(9) { height: 20px; animation-delay: 0.8s; }

        @keyframes mining {
            0%, 100% { transform: scaleY(1); opacity: 0.6; }
            50% { transform: scaleY(2); opacity: 1; }
        }

        .error-code {
            font-family: 'Orbitron', sans-serif;
            font-size: 160px;
            font-weight: 900;
            color: #a855f7;
            text-shadow:
                0 0 10px #a855f7,
                0 0 20px #a855f7,
                0 0 40px #a855f7,
                0 0 80px rgba(168, 85, 247, 0.3);
            line-height: 1;
            margin-bottom: 0.5rem;
            position: relative;
        }

        .error-code::before,
        .error-code::after {
            content: '503';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
        }

        .error-code::before {
            color: #00ffa3;
            animation: glitch-1 5s infinite;
            clip-path: polygon(0 0, 100% 0, 100% 33%, 0 33%);
        }

        .error-code::after {
            color: #00d4ff;
            animation: glitch-2 5s infinite;
            clip-path: polygon(0 66%, 100% 66%, 100% 100%, 0 100%);
        }

        @keyframes glitch-1 {
            0%, 92%, 100% { transform: translate(0); }
            94% { transform: translate(-2px, 1px); }
            96% { transform: translate(2px, -1px); }
        }

        @keyframes glitch-2 {
            0%, 93%, 100% { transform: translate(0); }
            95% { transform: translate(1px, 2px); }
            97% { transform: translate(-1px, -1px); }
        }

        .hash-display {
            font-size: 0.75rem;
            color: rgba(168, 85, 247, 0.4);
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
            background: rgba(168, 85, 247, 0.05);
            border: 1px solid rgba(168, 85, 247, 0.2);
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .terminal-line {
            color: rgba(168, 85, 247, 0.7);
            font-size: 0.85rem;
            margin: 0.3rem 0;
        }

        .terminal-line .prompt { color: #a855f7; }
        .terminal-line .info { color: #00d4ff; }
        .terminal-line .warning { color: #ffd700; }
        .terminal-line .success { color: #00ffa3; }

        .progress-bar {
            width: 100%;
            height: 4px;
            background: rgba(168, 85, 247, 0.1);
            border-radius: 2px;
            margin: 1.5rem 0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 60%;
            background: linear-gradient(90deg, #a855f7, #00d4ff);
            border-radius: 2px;
            animation: progressPulse 2s ease-in-out infinite;
            box-shadow: 0 0 10px rgba(168, 85, 247, 0.5);
        }

        @keyframes progressPulse {
            0%, 100% { width: 30%; }
            50% { width: 80%; }
        }

        .node-status {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .node-item {
            text-align: center;
        }

        .node-label {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.4);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 0.3rem;
        }

        .node-value {
            font-family: 'Orbitron', sans-serif;
            font-size: 0.85rem;
        }

        .node-value.syncing {
            color: #ffd700;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.4);
            animation: blink 1s infinite;
        }

        .node-value.offline {
            color: #ff0040;
            text-shadow: 0 0 10px rgba(255, 0, 64, 0.4);
        }

        .node-value.online {
            color: #00ffa3;
            text-shadow: 0 0 10px rgba(0, 255, 163, 0.4);
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
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
            background: #a855f7;
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
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.5), 0 0 40px rgba(168, 85, 247, 0.2);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: #a855f7;
            border: 1px solid rgba(168, 85, 247, 0.4);
        }

        .btn-secondary:hover {
            border-color: #a855f7;
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.2);
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
        <div class="particle"></div><div class="particle"></div><div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="error-container">
        <div class="mining-animation">
            <div class="mining-bar"></div><div class="mining-bar"></div><div class="mining-bar"></div>
            <div class="mining-bar"></div><div class="mining-bar"></div><div class="mining-bar"></div>
            <div class="mining-bar"></div><div class="mining-bar"></div><div class="mining-bar"></div>
        </div>

        <div class="error-code">503</div>

        <div class="hash-display">NODE: 0xMAINTENANCE...CHAIN_SYNC_IN_PROGRESS_PLEASE_WAIT</div>

        <h1 class="error-title">Node Maintenance</h1>

        <div class="node-status">
            <div class="node-item">
                <div class="node-label">Node</div>
                <div class="node-value offline">Offline</div>
            </div>
            <div class="node-item">
                <div class="node-label">Chain</div>
                <div class="node-value syncing">Syncing</div>
            </div>
            <div class="node-item">
                <div class="node-label">Peers</div>
                <div class="node-value online">Connected</div>
            </div>
        </div>

        <div class="terminal-box">
            <div class="terminal-line"><span class="prompt">$</span> node.status()</div>
            <div class="terminal-line"><span class="warning">MAINTENANCE:</span> Node is currently being upgraded</div>
            <div class="terminal-line"><span class="info">INFO:</span> Chain synchronization in progress...</div>
            <div class="terminal-line"><span class="success">ETA:</span> Node will be back online shortly</div>
        </div>

        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>

        <p class="error-message">
            Node sedang dalam pemeliharaan dan sinkronisasi chain. Sistem akan kembali online secepatnya. Terima kasih atas kesabaran Anda.
        </p>

        <div class="error-actions">
            <a href="javascript:location.reload()" class="btn btn-primary">Reconnect Node</a>
            <a href="{{ url('/') }}" class="btn btn-secondary">Genesis Block</a>
        </div>
    </div>
</body>
</html>
