<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prava — Enterprise Resource Planning</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #0e0e0e;
            color: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Subtle grid background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
            background-size: 80px 80px;
            pointer-events: none;
            z-index: 0;
        }

        /* Soft glow accent */
        .glow-accent {
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            filter: blur(150px);
            opacity: 0.04;
            pointer-events: none;
            z-index: 0;
        }

        .glow-1 {
            top: -200px;
            right: -100px;
            background: #ffffff;
        }

        .glow-2 {
            bottom: -200px;
            left: -100px;
            background: #ffffff;
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            padding: 1.5rem 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(14, 14, 14, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 44px;
            width: auto;
        }

        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.5rem;
            background: #fff;
            color: #0e0e0e;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .nav-btn:hover {
            background: #e0e0e0;
        }

        .nav-btn svg {
            width: 16px;
            height: 16px;
        }

        /* Hero Section */
        .hero {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 8rem 2rem 6rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 500;
            color: #888;
            letter-spacing: 0.03em;
            margin-bottom: 2.5rem;
        }

        .hero-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #4ade80;
            border-radius: 50%;
        }

        .hero-logo {
            margin-bottom: 2.5rem;
        }

        .hero-logo img {
            height: 100px;
            width: auto;
        }

        .hero-company-logo {
            margin-bottom: 2rem;
        }

        .hero-company-logo img {
            height: 40px;
            width: auto;
            opacity: 0.7;
        }

        .hero h1 {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1.15;
            margin-bottom: 1rem;
            max-width: 600px;
        }

        .hero p {
            font-size: 1.05rem;
            color: #777;
            line-height: 1.7;
            max-width: 460px;
            font-weight: 300;
            margin-bottom: 2.5rem;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 2rem;
            background: #fff;
            color: #0e0e0e;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: #e0e0e0;
            transform: translateY(-1px);
        }

        .btn-primary svg {
            width: 18px;
            height: 18px;
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 2rem;
            background: transparent;
            color: #999;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 400;
            border: 1px solid #2a2a2a;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            color: #fff;
            border-color: #444;
        }

        /* Divider */
        .section-divider {
            width: 60px;
            height: 1px;
            background: #333;
            margin: 0 auto;
        }

        /* Features Section */
        .features {
            position: relative;
            z-index: 1;
            padding: 6rem 2rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        .section-label {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #555;
            text-align: center;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            text-align: center;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1rem;
            color: #666;
            text-align: center;
            max-width: 480px;
            margin: 0 auto 4rem;
            font-weight: 300;
            line-height: 1.6;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            background: #1a1a1a;
            border: 1px solid #1a1a1a;
            border-radius: 12px;
            overflow: hidden;
        }

        .feature-card {
            background: #0e0e0e;
            padding: 2.5rem 2rem;
            transition: background 0.3s ease;
        }

        .feature-card:hover {
            background: #141414;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #2a2a2a;
            border-radius: 10px;
            margin-bottom: 1.25rem;
            color: #888;
        }

        .feature-icon svg {
            width: 20px;
            height: 20px;
        }

        .feature-card h3 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 0.6rem;
            letter-spacing: -0.01em;
        }

        .feature-card p {
            font-size: 0.82rem;
            color: #666;
            line-height: 1.6;
            font-weight: 300;
        }



        /* Footer */
        footer {
            position: relative;
            z-index: 1;
            padding: 3rem 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-left {
            font-size: 0.8rem;
            color: #444;
            font-weight: 400;
        }

        .footer-left strong {
            color: #666;
            font-weight: 500;
        }

        .footer-right {
            font-size: 0.75rem;
            color: #333;
            letter-spacing: 0.05em;
        }

        /* Responsive */
        @media (max-width: 768px) {
            nav {
                padding: 1rem 1.5rem;
            }

            .hero {
                padding: 7rem 1.5rem 4rem;
            }

            .hero-actions {
                flex-direction: column;
                width: 100%;
                max-width: 300px;
            }

            .btn-primary,
            .btn-secondary {
                width: 100%;
                justify-content: center;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            footer {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="glow-accent glow-1"></div>
<div class="glow-accent glow-2"></div>

<!-- Navigation -->
<nav>
    <div class="logo">
        <img src="{{ asset('logo-darkmode.png') }}" alt="Prava">
    </div>
    <a href="{{ route('filament.admin.auth.login') }}" class="nav-btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" y1="12" x2="3" y2="12"/>
        </svg>
        Masuk
    </a>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-company-logo">
        <img src="https://pratamatechsolution.co.id/pst-brand-logo-bg-none.png" alt="PT Pratama Teknologi Solusi">
    </div>

    <div class="hero-logo">
        <img src="{{ asset('logo-darkmode.png') }}" alt="Prava">
    </div>

    <h1>Sistem ERP Internal</h1>

    <p>
        Platform terpadu untuk mengelola proyek, tiket, tim, keuangan, dan pelaporan operasional perusahaan.
    </p>

    <div class="hero-actions">
        <a href="{{ route('filament.admin.auth.login') }}" class="btn-primary">
            Masuk
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/>
                <polyline points="12 5 19 12 12 19"/>
            </svg>
        </a>
    </div>
</section>

<div class="section-divider"></div>

<!-- Features -->
<section class="features" id="fitur">
    <div class="section-label">Modul</div>
    <h2 class="section-title">Fitur yang Tersedia</h2>
    <p class="section-subtitle">Modul-modul yang terintegrasi dalam sistem Prava.</p>

    <div class="features-grid">
        <!-- Feature 1 -->
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/>
                    <rect x="14" y="3" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/>
                </svg>
            </div>
            <h3>Manajemen Proyek</h3>
            <p>Pengelolaan proyek dengan timeline, milestone, dan tracking progres.</p>
        </div>

        <!-- Feature 2 -->
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <h3>Tim & Kolaborasi</h3>
            <p>Pengaturan anggota tim, peran, dan koordinasi antar departemen.</p>
        </div>

        <!-- Feature 3 -->
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <h3>Keuangan</h3>
            <p>Pencatatan anggaran, pengeluaran, dan laporan keuangan.</p>
        </div>

        <!-- Feature 4 -->
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
            <h3>Tiket & Helpdesk</h3>
            <p>Sistem tiket untuk pencatatan permintaan dan pelacakan penyelesaian.</p>
        </div>

        <!-- Feature 5 -->
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
            </div>
            <h3>Laporan & Analitik</h3>
            <p>Dashboard analitik dan laporan yang dapat diekspor.</p>
        </div>

        <!-- Feature 6 -->
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <h3>Kontrol Akses</h3>
            <p>Manajemen hak akses berbasis peran untuk setiap pengguna.</p>
        </div>
    </div>
</section>



<!-- Footer -->
<footer>
    <div class="footer-left">
        &copy; {{ date('Y') }} <strong>PT Pratama Teknologi Solusi</strong> — All rights reserved.
    </div>
    <div class="footer-right">
        Prava ERP v1.0
    </div>
</footer>

</body>
</html>
