<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ config('app.name') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <meta name="theme-color" content="#14b8a6">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <style>
        .theme-toggle {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .theme-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }
        
        .theme-toggle svg {
            width: 22px;
            height: 22px;
            stroke: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .theme-icon {
            position: absolute;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        
        .theme-icon.hidden {
            opacity: 0;
            transform: rotate(180deg);
        }
        
        .auth-logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .auth-logo-container img {
            height: 70px;
            width: auto;
            object-fit: contain;
            background: transparent;
            border-radius: 10px;
        }

        /* Dark mode: tambah brightness */
        body.dark-mode .auth-logo-container img {
            filter: brightness(1.3) contrast(1.1);
        }
        
        .auth-header h1 {
            margin-top: 0;
        }
        
        /* Responsive Mobile */
        @media (max-width: 768px) {
            .auth-container {
                padding: 1rem;
                align-items: flex-start;
                padding-top: 2rem;
            }
            
            .auth-card {
                width: 100%;
                max-width: 100%;
                padding: 1.5rem;
                margin: 0;
            }
            
            .auth-logo-container {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.75rem;
            }
            
            .auth-logo-container img {
                height: 50px;
            }
            
            .auth-header h1 {
                font-size: 1.5rem;
            }
            
            .auth-header p {
                font-size: 0.9rem;
            }
            
            .form-group label {
                font-size: 0.9rem;
            }
            
            .btn-primary {
                padding: 0.85rem;
                font-size: 1rem;
            }
            
            .theme-toggle {
                top: 1rem;
                right: 1rem;
                width: 44px;
                height: 44px;
            }
        }
        
        @media (max-width: 480px) {
            .auth-card {
                padding: 1.25rem;
            }
            
            .auth-logo-container img {
                height: 45px;
            }
            
            .auth-header h1 {
                font-size: 1.35rem;
            }
        }
    </style>
</head>
<body>
    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
        <svg class="theme-icon sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
        <svg class="theme-icon moon-icon hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
    </button>

    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo-container">
                    <img src="{{ asset('logo-pertamina.png') }}" alt="Pertamina Logo">
                    <img src="{{ asset('logo-agm.png') }}" alt="AGM Logo">
                </div>
                <h1>{{ config('app.name') }}</h1>
                <p>Warehouse & Inventory</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom: 1.5rem; padding: 0.75rem 1rem;">
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="nama@perusahaan.com" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="password">Kata Sandi</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem;">
                    <input type="checkbox" name="remember" id="remember" style="cursor: pointer;">
                    <label for="remember" style="margin: 0; cursor: pointer; font-size: 0.85rem; user-select: none;">Ingat Saya di Perangkat Ini</label>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.85rem;">Masuk</button>
            </form>
            
        </div>
    </div>
    
    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('themeToggle');
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');
        const html = document.documentElement;
        
        // Check for saved theme preference or default to 'light'
        const currentTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', currentTheme);
        
        // Update icon visibility based on current theme
        if (currentTheme === 'dark') {
            sunIcon.classList.add('hidden');
            moonIcon.classList.remove('hidden');
        }
        
        themeToggle.addEventListener('click', function() {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Toggle icons
            if (newTheme === 'dark') {
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            } else {
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            }
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            });
        }, 5000);
    </script>
</body>
</html>
