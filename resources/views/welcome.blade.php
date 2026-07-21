<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daily Report | Warehouse & Inventory</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <meta name="theme-color" content="#14b8a6">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ============================================
           DESIGN TOKENS — LIGHT MODE (DEFAULT)
           ============================================ */
        :root,
        [data-theme="light"] {
            --bg-primary:      #f8fafc;
            --bg-secondary:    #f1f5f9;
            --bg-card:         #ffffff;
            --bg-header:       rgba(248, 250, 252, 0.85);
            --bg-stats:        #0f172a;

            --accent-blue:     #0f9488; /* Teal / Toska */
            --accent-orange:   #d97706;
            --accent-green:    #059669;
            --gradient-hero:   linear-gradient(135deg, #0f9488 0%, #0d9488 100%);

            --text-primary:    #0f172a;
            --text-secondary:  #475569;
            --text-muted:      #94a3b8;
            --text-on-dark:    #f8fafc;

            --border-color:    #e2e8f0;
            --border-soft:     rgba(0, 0, 0, 0.06);

            --shadow-sm:       0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md:       0 4px 16px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
            --shadow-lg:       0 10px 30px rgba(0,0,0,0.1);
            --shadow-btn-blue: 0 4px 16px rgba(15, 148, 136, 0.3);
        }

        /* ============================================
           DARK MODE TOKENS
           ============================================ */
        [data-theme="dark"] {
            --bg-primary:      #030712;
            --bg-secondary:    #0b0f19;
            --bg-card:         rgba(17, 24, 39, 0.8);
            --bg-header:       rgba(3, 7, 18, 0.85);
            --bg-stats:        #060c18;

            --accent-blue:     #0f9488; /* Teal / Toska */
            --accent-orange:   #f59e0b;
            --accent-green:    #10b981;
            --gradient-hero:   linear-gradient(135deg, #0f9488 0%, #0d9488 100%);

            --text-primary:    #f9fafb;
            --text-secondary:  #94a3b8;
            --text-muted:      #64748b;
            --text-on-dark:    #f9fafb;

            --border-color:    rgba(255, 255, 255, 0.07);
            --border-soft:     rgba(255, 255, 255, 0.05);

            --shadow-sm:       0 1px 3px rgba(0,0,0,0.4);
            --shadow-md:       0 4px 16px rgba(0,0,0,0.5);
            --shadow-lg:       0 10px 30px rgba(0,0,0,0.6);
            --shadow-btn-blue: 0 4px 20px rgba(15, 148, 136, 0.4);
        }

        /* ============================================
           BASE STYLES
           ============================================ */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            overflow-x: hidden;
            line-height: 1.6;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Ambient Glow */
        .ambient-glow-1 {
            position: absolute;
            width: 700px; height: 700px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(15, 148, 136, 0.05) 0%, transparent 70%);
            top: -10%; right: -5%;
            z-index: 0; pointer-events: none;
        }
        [data-theme="dark"] .ambient-glow-1 {
            background: radial-gradient(circle, rgba(15, 148, 136, 0.07) 0%, transparent 70%);
        }
        .ambient-glow-2 {
            position: absolute;
            width: 550px; height: 550px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.04) 0%, transparent 70%);
            bottom: 20%; left: -8%;
            z-index: 0; pointer-events: none;
        }

        /* ============================================
           HEADER / NAVBAR
           ============================================ */
        header {
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            padding: 1.1rem 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            background: var(--bg-header);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border-color);
            transition: background 0.3s ease, border-color 0.3s ease;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo-badge {
            width: 56px; 
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            overflow: hidden;
        }

        .logo-badge img {
            width: 52px;
            height: 52px;
            object-fit: contain;
            background: transparent;
            border-radius: 10px;
        }

        /* Dark mode: tambah brightness */
        body.dark-mode .logo-badge img {
            filter: brightness(1.3) contrast(1.1);
        }

        .brand-name {
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: -0.02em;
            color: var(--text-primary);
            line-height: 1.1;
        }

        .brand-sub {
            font-size: 0.72rem;
            color: var(--text-secondary);
        }

        /* Navbar Right Side */
        .nav-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Dark/Light Toggle Button */
        .theme-toggle {
            width: 40px; height: 40px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .icon-sun, .icon-moon { pointer-events: none; }
        [data-theme="light"] .icon-moon { display: none; }
        [data-theme="dark"] .icon-sun { display: none; }

        /* ===== NAVBAR LOGIN BUTTON (Sesuai Gambar Mockup) ===== */
        nav a.login-btn {
            background-color: var(--accent-blue);
            color: #ffffff;
            padding: 0.65rem 1.6rem;
            border-radius: 12px; /* rounded elegant */
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            white-space: nowrap;
            box-shadow: 0 4px 14px rgba(15, 148, 136, 0.25);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        nav a.login-btn:hover {
            background-color: #0d9488;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(15, 148, 136, 0.4);
        }

        nav a.login-btn:active {
            transform: translateY(0) scale(0.98);
        }

        nav a.login-btn .btn-arrow {
            display: inline-flex;
            align-items: center;
            transition: transform 0.3s ease;
        }

        nav a.login-btn:hover .btn-arrow {
            transform: translateX(4px);
        }

        /* ============================================
           HERO SECTION
           ============================================ */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 8rem 1.5rem 5rem 1.5rem;
            position: relative;
            z-index: 10;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(15, 148, 136, 0.08);
            color: var(--accent-blue);
            border: 1px solid rgba(15, 148, 136, 0.18);
            padding: 0.4rem 1.3rem;
            border-radius: 30px;
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 1.75rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        .hero-badge-dot {
            width: 7px; height: 7px;
            background-color: var(--accent-orange);
            border-radius: 50%;
        }

        .hero h1 {
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
            max-width: 840px;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }

        .hero h1 span {
            background: var(--gradient-hero);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.15rem;
            color: var(--text-secondary);
            max-width: 640px;
            margin-bottom: 2.5rem;
            font-weight: 300;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* Hero Primary Button */
        .btn-primary {
            position: relative;
            overflow: hidden;
            background: var(--gradient-hero);
            color: #fff;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            letter-spacing: 0.01em;
            box-shadow: var(--shadow-btn-blue);
            transition: all 0.35s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0; left: -75%;
            width: 50%; height: 100%;
            background: linear-gradient(
                to right,
                rgba(255,255,255,0) 0%,
                rgba(255,255,255,0.28) 50%,
                rgba(255,255,255,0) 100%
            );
            transform: skewX(-20deg);
        }

        .btn-primary:hover::before {
            animation: shimmer 0.65s ease forwards;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(15, 148, 136, 0.45);
        }

        .btn-primary .btn-arrow {
            display: inline-flex;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .btn-primary:hover .btn-arrow {
            transform: translateX(4px);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-primary);
            padding: 1rem 2.25rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            border: 1.5px solid var(--border-color);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-outline:hover {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
            background: rgba(15, 148, 136, 0.05);
            transform: translateY(-2px);
        }

        @keyframes shimmer {
            0%   { left: -75%; }
            100% { left: 125%; }
        }

        /* Scroll indicator */
        .scroll-indicator {
            position: absolute;
            bottom: 2.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            font-size: 0.78rem;
            text-decoration: none;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .scroll-indicator:hover { opacity: 1; }

        .scroll-dot {
            width: 6px; height: 10px;
            background-color: var(--accent-orange);
            border-radius: 3px;
            animation: bounce 1.6s infinite;
        }

        /* ============================================
           STATS BAR
           ============================================ */
        .stats {
            padding: 4rem 8%;
            background-color: var(--bg-stats);
            border-top: 1px solid var(--border-soft);
            border-bottom: 1px solid var(--border-soft);
            position: relative;
            z-index: 10;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2.5rem;
            max-width: 960px;
            margin: 0 auto;
            text-align: center;
        }

        .stat-item h3 {
            font-size: 2.75rem;
            font-weight: 800;
            background: var(--gradient-hero);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.25rem;
        }

        .stat-item p {
            color: #94a3b8;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ============================================
           FEATURES SECTION
           ============================================ */
        .features {
            padding: 7rem 8% 6rem 8%;
            position: relative;
            z-index: 10;
            background-color: var(--bg-primary);
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-label {
            display: inline-block;
            background: rgba(15, 148, 136, 0.08);
            color: var(--accent-blue);
            border: 1px solid rgba(15, 148, 136, 0.15);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 1rem;
        }

        .section-header h2 {
            font-size: 2.4rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .section-header p {
            color: var(--text-secondary);
            max-width: 580px;
            margin: 0 auto;
            font-size: 1.05rem;
            font-weight: 300;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            padding: 2.75rem 2.25rem;
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
            transition: all 0.35s ease;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(15, 148, 136, 0.25);
        }

        .feature-icon {
            width: 52px; height: 52px;
            background: rgba(15, 148, 136, 0.08);
            color: var(--accent-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            margin-bottom: 1.5rem;
        }

        .feature-card.amber .feature-icon {
            background: rgba(217, 119, 6, 0.08);
            color: var(--accent-orange);
        }

        .feature-card.green .feature-icon {
            background: rgba(5, 150, 105, 0.08);
            color: var(--accent-green);
        }

        .feature-card h3 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--text-primary);
        }

        .feature-card p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.65;
        }

        /* ============================================
           WORKFLOW / ROLES SECTION
           ============================================ */
        .roles-section {
            padding: 6rem 8%;
            background-color: var(--bg-secondary);
            position: relative;
            z-index: 10;
            transition: background-color 0.3s ease;
        }

        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.75rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        .role-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 18px;
            padding: 2.25rem 2rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.35s ease;
        }

        .role-card:hover {
            transform: scale(1.025);
            box-shadow: var(--shadow-md);
        }

        .role-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .role-title {
            font-weight: 800;
            font-size: 1.05rem;
            color: var(--text-primary);
            letter-spacing: 0.02em;
        }

        .role-badge {
            font-size: 0.73rem;
            padding: 0.25rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-fuelman { background: rgba(15, 148, 136, 0.1); color: var(--accent-blue); }
        .badge-gl      { background: rgba(217, 119, 6, 0.1);  color: var(--accent-orange); }
        .badge-spv     { background: rgba(5, 150, 105, 0.1);  color: var(--accent-green); }

        .role-list { list-style: none; }

        .role-list li {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 0.7rem;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
        }

        .role-list li .check {
            width: 17px; height: 17px;
            flex-shrink: 0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            font-weight: 700;
            margin-top: 2px;
        }

        .check-blue  { background: rgba(15, 148, 136, 0.12); color: var(--accent-blue); }
        .check-amber { background: rgba(217, 119, 6, 0.12);  color: var(--accent-orange); }
        .check-green { background: rgba(5, 150, 105, 0.12);  color: var(--accent-green); }

        /* ============================================
           FOOTER
           ============================================ */
        footer {
            padding: 3.5rem 8%;
            border-top: 1px solid var(--border-color);
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
            background-color: var(--bg-primary);
            transition: background-color 0.3s ease;
        }

        footer p.copyright { margin-bottom: 0.4rem; color: var(--text-secondary); }
        footer p.site-info  { font-size: 0.75rem; }

        /* ============================================
           SCROLL REVEAL ANIMATION
           ============================================ */
        .reveal {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1),
                        transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        .delay-1 { transition-delay: 0.12s; }
        .delay-2 { transition-delay: 0.24s; }
        .delay-3 { transition-delay: 0.36s; }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(6px); }
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 768px) {
            header { 
                padding: 1rem 5%; 
                flex-direction: row;
                justify-content: space-between;
            }
            
            .logo-container {
                gap: 0.5rem;
            }
            
            .brand-name {
                font-size: 1rem;
            }
            
            .brand-sub {
                font-size: 0.65rem;
            }
            
            .auth-buttons {
                gap: 0.5rem;
            }
            
            .auth-buttons .btn {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
            
            .hero { 
                padding: 3rem 5% 5rem;
            }
            
            .hero h1 { 
                font-size: 2rem;
                line-height: 1.2;
            }
            
            .hero h2 {
                font-size: 1.5rem;
            }
            
            .hero p  { 
                font-size: 1rem;
                max-width: 100%;
            }
            
            .cta-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .cta-buttons .btn {
                width: 100%;
                justify-content: center;
            }
            
            .features, .roles-section { 
                padding: 3rem 5%; 
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .roles-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }
            
            footer {
                padding: 2rem 5%;
            }
        }
        
        @media (max-width: 480px) {
            .logo-badge {
                width: 44px;
                height: 44px;
            }
            
            .logo-badge img {
                width: 40px;
                height: 40px;
            }
            
            .hero h1 {
                font-size: 1.75rem;
            }
            
            .hero h2 {
                font-size: 1.25rem;
            }
            
            .auth-buttons .btn {
                padding: 0.5rem 0.85rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>

    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <!-- ============ HEADER ============ -->
    <header>
        <div class="logo-container">
            <div class="logo-badge">
                <img src="{{ asset('favicon.png') }}" alt="Daily Report Logo">
            </div>
            <div>
                <div class="brand-name">DAILY REPORT</div>
                <div class="brand-sub">Warehouse & Inventory</div>
            </div>
        </div>

        <div class="nav-right">
            <!-- Theme Toggle -->
            <button class="theme-toggle" id="themeToggle" title="Toggle Mode">
                <!-- Sun icon -->
                <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"></circle>
                    <line x1="12" y1="1" x2="12" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="23"></line>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                    <line x1="1" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="12" x2="23" y2="12"></line>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                </svg>
                <!-- Moon icon -->
                <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>

            <!-- Auth Button (Mockup Flat Style) -->
            @auth
                <a href="{{ url('/dashboard') }}" class="login-btn" style="background-color: #0f9488 !important; color: #ffffff !important; padding: 0.6rem 1.5rem !important; border-radius: 12px !important; font-size: 0.88rem !important; font-weight: 700 !important; text-decoration: none !important; display: inline-flex !important; align-items: center !important; gap: 0.5rem !important; border: none !important; box-shadow: 0 4px 14px rgba(15, 148, 136, 0.25) !important;">
                    Dashboard
                    <span class="btn-arrow" style="display: inline-flex; align-items: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>
            @else
                <a href="{{ route('login') }}" class="login-btn" style="background-color: #0f9488 !important; color: #ffffff !important; padding: 0.6rem 1.5rem !important; border-radius: 12px !important; font-size: 0.88rem !important; font-weight: 700 !important; text-decoration: none !important; display: inline-flex !important; align-items: center !important; gap: 0.5rem !important; border: none !important; box-shadow: 0 4px 14px rgba(15, 148, 136, 0.25) !important;">
                    Masuk
                    <span class="btn-arrow" style="display: inline-flex; align-items: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>
            @endauth
        </div>
    </header>

    <!-- ============ HERO ============ -->
    <section class="hero">
        <div class="hero-badge reveal">
            <span class="hero-badge-dot"></span>
            Integrated Inventory App
        </div>
        <h1 class="reveal delay-1">
            Sistem Laporan Harian<br>
            <span>Monitoring BBM</span> Digital
        </h1>
        <p class="reveal delay-2">
            Automasi pencatatan data sounding harian, kalkulasi volume stok (SOH) tangki secara otomatis, pengawasan transfer solar, dan log pemakaian flowmeter yang akurat dan aman.
        </p>
        <div class="hero-actions reveal delay-3">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-primary">
                    Ke Dashboard
                    <span class="btn-arrow">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-primary">
                    Akses Aplikasi
                    <span class="btn-arrow">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </span>
                </a>
            @endauth
            <a href="#fitur" class="btn-outline">Jelajahi Fitur</a>
        </div>

        <a href="#fitur" class="scroll-indicator">
            <span>Scroll ke bawah</span>
            <div class="scroll-dot"></div>
        </a>
    </section>

    <!-- ============ STATS BAR ============ -->
    <section class="stats" id="statistik">
        <div class="stats-grid">
            <div class="stat-item reveal">
                <h3>100%</h3>
                <p>Digital & Paperless</p>
            </div>
            <div class="stat-item reveal delay-1">
                <h3>Real-Time</h3>
                <p>SOH Volume Calculation</p>
            </div>
            <div class="stat-item reveal delay-2">
                <h3>3-Level</h3>
                <p>Approval & Verification</p>
            </div>
        </div>
    </section>

    <!-- ============ FEATURES ============ -->
    <section class="features" id="fitur">
        <div class="section-header reveal">
            <div class="section-label">Fitur Utama</div>
            <h2>Semua yang Anda Butuhkan</h2>
            <p>Dibangun dengan logika bisnis yang disesuaikan untuk manajemen inventory migas di lapangan secara akurat dan efisien.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card reveal delay-1">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <h3>Form Laporan Harian</h3>
                <p>Input data laporan sounding harian dan angka flowmeter dengan form tabular yang bersih, terstruktur, dan langsung terhitung secara otomatis di setiap baris.</p>
            </div>

            <div class="feature-card amber reveal delay-2">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                        <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"></path>
                    </svg>
                </div>
                <h3>Kalkulasi SOH Tangki</h3>
                <p>Volume stok sisa (SOH) dihitung otomatis menggunakan tabel kalibrasi interpolasi milimeter ke liter — meniru persis metode manual fisik di lapangan.</p>
            </div>

            <div class="feature-card green reveal delay-3">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                </div>
                <h3>Monitoring Flowmeter</h3>
                <p>Log pemakaian solar harian dan pergerakan transfer bahan bakar antar tangki untuk mendeteksi selisih dan deviasi data secara cepat dan presisi.</p>
            </div>
        </div>
    </section>

    <!-- ============ ROLES SECTION ============ -->
    <section class="roles-section">
        <div class="section-header reveal">
            <div class="section-label">Alur Verifikasi</div>
            <h2>Hak Akses & Verifikasi Berjenjang</h2>
            <p>Tiga tingkatan peran pengguna memastikan laporan yang masuk tervalidasi, terverifikasi, dan disetujui sebelum dianggap final.</p>
        </div>

        <div class="roles-grid">
            <div class="role-card reveal">
                <div class="role-header">
                    <span class="role-title">FUELMAN</span>
                    <span class="role-badge badge-fuelman">Operator</span>
                </div>
                <ul class="role-list">
                    <li><span class="check check-blue">✓</span> Input sounding harian tangki</li>
                    <li><span class="check check-blue">✓</span> Catat angka flowmeter pemakaian</li>
                    <li><span class="check check-blue">✓</span> Log transfer solar antar tangki</li>
                    <li><span class="check check-blue">✓</span> Kirim laporan ke Group Leader</li>
                </ul>
            </div>

            <div class="role-card reveal delay-1">
                <div class="role-header">
                    <span class="role-title">GROUP LEADER</span>
                    <span class="role-badge badge-gl">Verifikator</span>
                </div>
                <ul class="role-list">
                    <li><span class="check check-amber">✓</span> Terima & review draft laporan</li>
                    <li><span class="check check-amber">✓</span> Periksa konsistensi data sounding</li>
                    <li><span class="check check-amber">✓</span> Validasi selisih volume & flowmeter</li>
                    <li><span class="check check-amber">✓</span> Teruskan laporan ke Supervisor</li>
                </ul>
            </div>

            <div class="role-card reveal delay-2">
                <div class="role-header">
                    <span class="role-title">SUPERVISOR</span>
                    <span class="role-badge badge-spv">Approval</span>
                </div>
                <ul class="role-list">
                    <li><span class="check check-green">✓</span> Otorisasi akhir laporan harian</li>
                    <li><span class="check check-green">✓</span> Lihat analisis dashboard performa</li>
                    <li><span class="check check-green">✓</span> Kelola data tangki & akun staf</li>
                    <li><span class="check check-green">✓</span> Kembalikan laporan untuk revisi</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ============ FOOTER ============ -->
    <footer>
        <p class="copyright">&copy; {{ date('Y') }} Daily Report. All rights reserved.</p>
        <p class="site-info">Warehouse & Inventory Management System &bull; v1.1</p>
    </footer>

    <!-- ============ SCRIPTS ============ -->
    <script>
        // --- Theme Toggle ---
        const html = document.documentElement;
        const toggleBtn = document.getElementById('themeToggle');

        // Load saved preference or default to light
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);

        toggleBtn.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            const next = current === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        });

        // --- Scroll Reveal ---
        document.addEventListener('DOMContentLoaded', () => {
            const reveals = document.querySelectorAll('.reveal');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                root: null,
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            reveals.forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>
