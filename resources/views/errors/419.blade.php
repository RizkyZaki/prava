<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Sesi Kadaluarsa</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }

        .error-code {
            font-size: 150px;
            font-weight: 900;
            color: rgba(255, 255, 255, 0.95);
            text-shadow: 4px 4px 0px rgba(0, 0, 0, 0.1);
            line-height: 1;
            margin-bottom: 1rem;
            animation: bounce 1s ease-in-out;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .error-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .error-message {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: rgba(102, 126, 234, 0.2);
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: rgba(102, 126, 234, 0.3);
            transform: translateY(-2px);
        }

        .icon {
            font-size: 100px;
            margin-bottom: 1rem;
            animation: rotate 4s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="icon">⏰</div>
        <div class="error-code">419</div>
        <h1 class="error-title">Sesi Kadaluarsa</h1>
        <p class="error-message">
            Sesi Anda telah kadaluarsa karena tidak ada aktivitas dalam waktu yang lama. Untuk keamanan Anda, silakan muat ulang halaman dan coba lagi.
        </p>
        <div class="error-actions">
            <a href="javascript:location.reload()" class="btn btn-primary">Muat Ulang Halaman</a>
            <a href="{{ url('/') }}" class="btn btn-secondary">Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
