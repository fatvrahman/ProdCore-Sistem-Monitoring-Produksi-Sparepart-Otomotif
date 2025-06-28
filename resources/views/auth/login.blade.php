<!DOCTYPE html>
<!-- File: resources/views/auth/login.blade.php -->
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Login - {{ config('app.name', 'ProdCore') }}</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    
    <!-- CSS Assets -->
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/auth.css') }}">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Custom Login Styles with Animated Background */
        body {
            margin: 0;
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background */
        .animated-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .animated-bg::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 30% 40%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 70% 70%, rgba(255, 255, 255, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.06) 0%, transparent 50%);
            animation: rotate 20s infinite linear;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
        }

        .gear {
            position: absolute;
            color: rgba(255, 255, 255, 0.1);
            animation: rotateGear 15s linear infinite;
        }

        .gear:nth-child(1) {
            font-size: 60px;
            top: 15%;
            left: 8%;
            animation-duration: 20s;
        }

        .gear:nth-child(2) {
            font-size: 80px;
            top: 60%;
            left: 85%;
            animation-duration: 25s;
            animation-direction: reverse;
        }

        .gear:nth-child(3) {
            font-size: 40px;
            top: 75%;
            left: 15%;
            animation-duration: 18s;
        }

        .gear:nth-child(4) {
            font-size: 100px;
            top: 8%;
            left: 75%;
            animation-duration: 30s;
            animation-direction: reverse;
        }

        .gear:nth-child(5) {
            font-size: 50px;
            top: 45%;
            left: 65%;
            animation-duration: 22s;
        }

        .gear:nth-child(6) {
            font-size: 35px;
            top: 25%;
            left: 45%;
            animation-duration: 16s;
            animation-direction: reverse;
        }

        @keyframes rotateGear {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }

        .login-logo .logo-image {
            max-width: 140px;
            height: 140px;
            width: 140px;
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
            display: block;
        }

        .login-logo .logo-fallback {
            width: 140px;
            height: 140px;
            background: linear-gradient(135deg, #435ebe 0%, #5a67d8 100%);
            border-radius: 20px;
            display: none;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            box-shadow: 0 8px 25px rgba(67, 94, 190, 0.3);
        }

        .login-logo .logo-fallback.show {
            display: flex;
        }

        .login-logo h2 {
            color: #435ebe;
            font-weight: 700;
            font-size: 28px;
            margin: 0;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            width: 100%;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: #435ebe;
            box-shadow: 0 0 0 0.2rem rgba(67, 94, 190, 0.25);
            background: white;
            outline: none;
        }

        .btn-login {
            background: linear-gradient(135deg, #435ebe 0%, #5a67d8 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
            cursor: pointer;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(67, 94, 190, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .form-check {
            margin: 20px 0;
            display: flex;
            align-items: center;
        }

        .form-check-input {
            margin-right: 8px;
        }

        .form-check-input:checked {
            background-color: #435ebe;
            border-color: #435ebe;
        }

        .form-check-label {
            color: #6c757d;
            font-size: 14px;
        }

        .login-footer {
            text-align: center;
            margin-top: 30px;
            color: #6c757d;
            font-size: 12px;
        }

        .login-footer .version {
            color: #435ebe;
            font-weight: 600;
        }

        /* Alert Styles */
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
            padding: 12px 16px;
            border: none;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }

        .is-invalid {
            border-color: #dc3545;
        }

        /* Loading Animation - Bouncing Dots */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .loading.show {
            display: flex;
        }

        .bouncing-dots {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: white;
            animation: bounce 1.4s ease-in-out infinite both;
        }

        .dot:nth-child(1) { animation-delay: -0.32s; }
        .dot:nth-child(2) { animation-delay: -0.16s; }
        .dot:nth-child(3) { animation-delay: 0s; }

        @keyframes bounce {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        /* Button Loading State */
        .btn-login.loading-state {
            position: relative;
            color: transparent;
            pointer-events: none;
        }

        .btn-login .loading-dots {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            gap: 4px;
        }

        .btn-login.loading-state .loading-dots {
            display: flex;
            align-items: center;
        }

        .btn-login .loading-dots .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: white;
            animation: bounce 1.4s ease-in-out infinite both;
        }

        .btn-login .loading-dots .dot:nth-child(1) { animation-delay: -0.32s; }
        .btn-login .loading-dots .dot:nth-child(2) { animation-delay: -0.16s; }
        .btn-login .loading-dots .dot:nth-child(3) { animation-delay: 0s; }

        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                margin: 20px;
                padding: 30px 20px;
            }
            
            .login-logo h2 {
                font-size: 24px;
            }

            .login-logo .logo-image,
            .login-logo .logo-fallback {
                max-width: 100px;
                width: 100px;
                height: 100px;
            }

            .login-logo .logo-fallback {
                font-size: 36px;
            }
        }
    </style>
</head>

<body>
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="floating-shapes">
            <i class="fas fa-cog gear"></i>
            <i class="fas fa-gear gear"></i>
            <i class="fas fa-cog gear"></i>
            <i class="fas fa-gear gear"></i>
            <i class="fas fa-cog gear"></i>
            <i class="fas fa-gear gear"></i>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading" id="loadingOverlay">
        <div class="bouncing-dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Logo & Title -->
            <div class="login-logo">
                <div class="logo-container">
                    <img class="logo-image" 
                         id="logoImage"
                         src="{{ asset('mazer/assets/static/images/logo/logo.png') }}" 
                         alt="ProdCore Logo">
                    <div class="logo-fallback" id="logoFallback">
                        <i class="fas fa-industry"></i>
                    </div>
                </div>
             
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">Email <span style="color: #dc3545;">*</span></label>
                    <input type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           placeholder="Masukkan email Anda"
                           required 
                           autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">Password <span style="color: #dc3545;">*</span></label>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           placeholder="Masukkan password Anda"
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        Ingat saya
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login" id="loginButton">
                    <span class="button-text">Masuk ke ProdCore</span>
                    <div class="loading-dots">
                        <div class="dot"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                    </div>
                </button>
            </form>

            <!-- Demo Credentials (Development Only) -->
            <!-- Removed demo credentials for production readiness -->

            <!-- Footer -->
            <div class="login-footer">
                <p>© {{ date('Y') }} ProdCore. Sistem manajemen produksi brakepad motor.</p>
                <p>Versi <span class="version">1.0.0</span></p>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle logo loading
            const logoImage = document.getElementById('logoImage');
            const logoFallback = document.getElementById('logoFallback');
            
            // Multiple logo path attempts
            const logoPaths = [
                '{{ asset("mazer/assets/static/images/logo/logo.png") }}',
                '{{ asset("mazer/assets/static/images/logo/logo.svg") }}',
                '{{ asset("images/logo.png") }}',
                '{{ asset("logo.png") }}',
                '{{ asset("assets/logo.png") }}'
            ];
            
            let currentLogoIndex = 0;
            
            function tryNextLogo() {
                if (currentLogoIndex < logoPaths.length) {
                    logoImage.src = logoPaths[currentLogoIndex];
                    currentLogoIndex++;
                } else {
                    // All logos failed, show fallback
                    logoImage.style.display = 'none';
                    logoFallback.classList.add('show');
                }
            }
            
            logoImage.onerror = function() {
                tryNextLogo();
            };
            
            logoImage.onload = function() {
                logoImage.style.display = 'block';
                logoFallback.classList.remove('show');
            };
            
            // Initial load attempt
            tryNextLogo();
            
            // Form loading state
            document.getElementById('loginForm').addEventListener('submit', function() {
                const button = document.getElementById('loginButton');
                button.classList.add('loading-state');
            });

            // Auto hide alerts
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 5000);

            // Form validation
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const button = document.getElementById('loginButton');

                if (!email || !password) {
                    e.preventDefault();
                    alert('Email dan password harus diisi!');
                    button.classList.remove('loading-state');
                    return;
                }

                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Format email tidak valid!');
                    button.classList.remove('loading-state');
                    return;
                }
            });
        });

        // Debug info for development
        @if(config('app.debug'))
        console.log('=== DEMO CREDENTIALS ===');
        console.log('Admin: admin@prodcore.com / admin123');
        console.log('Operator: budi.operator@prodcore.com / password');
        console.log('QC: maya.qc@prodcore.com / password');
        console.log('Gudang: tono.gudang@prodcore.com / password');
        console.log('========================');
        @endif
    </script>
</body>
</html>