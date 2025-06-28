{{-- File: resources/views/errors/404.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | {{ config('app.name', 'ProdCore') }}</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/images/logo/favicon.ico" type="image/x-icon">
    
    <!-- Mazer CSS via CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/app-dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/compiled/css/iconly.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Nunito', sans-serif;
        }

        .error-container {
            text-align: center;
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            width: 90%;
            position: relative;
            overflow: hidden;
        }

        .error-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            background-size: 200% 100%;
            animation: gradient 3s ease infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .error-number {
            font-size: 8rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            line-height: 1;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .error-title {
            font-size: 2rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 1rem 0;
        }

        .error-subtitle {
            font-size: 1.1rem;
            color: #7f8c8d;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .error-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin: 2rem 0;
            text-align: left;
            border-left: 4px solid #667eea;
        }

        .error-details h6 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .error-details p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: 2px solid;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-color: transparent;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
            text-decoration: none;
        }

        .btn-outline {
            background: transparent;
            color: #667eea;
            border-color: #667eea;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            text-decoration: none;
        }

        .help-section {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
        }

        .help-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .help-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .help-item:hover {
            transform: translateY(-2px);
            background: #e9ecef;
        }

        .help-item i {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .help-item h6 {
            margin: 0.5rem 0;
            color: #2c3e50;
        }

        .help-item p {
            margin: 0;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .footer-info {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .error-animation {
            width: 150px;
            height: 150px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: float 3s ease-in-out infinite;
        }

        .error-animation i {
            font-size: 4rem;
            color: white;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @media (max-width: 768px) {
            .error-container {
                padding: 2rem;
                margin: 1rem;
            }
            
            .error-number {
                font-size: 5rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }

            .help-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .error-container {
                background: #2c3e50;
                color: white;
            }

            .error-title {
                color: white;
            }

            .error-details {
                background: #34495e;
                border-left-color: #667eea;
            }

            .error-details h6 {
                color: white;
            }

            .help-item {
                background: #34495e;
                color: white;
            }

            .help-item:hover {
                background: #3d566e;
            }

            .help-item h6 {
                color: white;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <!-- Error Animation Icon -->
        <div class="error-animation">
            <i class="fas fa-search"></i>
        </div>

        <!-- Error Number -->
        <h1 class="error-number">404</h1>
        
        <!-- Error Title -->
        <h2 class="error-title">Halaman Tidak Ditemukan</h2>
        
        <!-- Error Description -->
        <p class="error-subtitle">
            Maaf, halaman yang Anda cari tidak dapat ditemukan. 
            Mungkin halaman telah dipindahkan, dihapus, atau URL yang dimasukkan salah.
        </p>

        <!-- Error Details -->
        <div class="error-details">
            <h6><i class="fas fa-info-circle me-2"></i>Detail Error:</h6>
            <p><strong>URL:</strong> {{ request()->fullUrl() }}</p>
            <p><strong>Method:</strong> {{ request()->method() }}</p>
            <p><strong>Waktu:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
            @if(auth()->check())
                <p><strong>User:</strong> {{ auth()->user()->name }} ({{ auth()->user()->role->display_name ?? 'No Role' }})</p>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="fas fa-home"></i>
                Kembali ke Dashboard
            </a>
            <a href="javascript:history.back()" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Halaman Sebelumnya
            </a>
        </div>

        <!-- Help Section -->
        <div class="help-section">
            <h6 style="color: #2c3e50; margin-bottom: 1rem;">
                <i class="fas fa-question-circle me-2"></i>
                Apa yang bisa Anda lakukan?
            </h6>
            
            <div class="help-grid">
                <div class="help-item">
                    <i class="fas fa-search"></i>
                    <h6>Cek URL</h6>
                    <p>Pastikan alamat website yang dimasukkan benar</p>
                </div>
                
                <div class="help-item">
                    <i class="fas fa-home"></i>
                    <h6>Dashboard</h6>
                    <p>Kembali ke halaman utama untuk navigasi yang benar</p>
                </div>
                
                <div class="help-item">
                    <i class="fas fa-sync-alt"></i>
                    <h6>Refresh</h6>
                    <p>Coba muat ulang halaman ini dalam beberapa menit</p>
                </div>
                
                <div class="help-item">
                    <i class="fas fa-headset"></i>
                    <h6>Bantuan</h6>
                    <p>Hubungi administrator jika masalah berlanjut</p>
                </div>
            </div>
        </div>

        <!-- Quick Navigation (Authenticated Users) -->
        @auth
        <div class="help-section">
            <h6 style="color: #2c3e50; margin-bottom: 1rem;">
                <i class="fas fa-compass me-2"></i>
                Navigasi Cepat
            </h6>
            
            <div class="help-grid">
                @if(in_array(auth()->user()->role->name, ['admin', 'operator']))
                <div class="help-item">
                    <a href="{{ route('productions.index') }}" style="text-decoration: none; color: inherit;">
                        <i class="fas fa-industry"></i>
                        <h6>Produksi</h6>
                        <p>Data produksi harian</p>
                    </a>
                </div>
                @endif

                @if(in_array(auth()->user()->role->name, ['admin', 'qc']))
                <div class="help-item">
                    <a href="{{ route('quality-controls.index') }}" style="text-decoration: none; color: inherit;">
                        <i class="fas fa-microscope"></i>
                        <h6>Quality Control</h6>
                        <p>Kontrol kualitas produk</p>
                    </a>
                </div>
                @endif

                @if(in_array(auth()->user()->role->name, ['admin', 'gudang']))
                <div class="help-item">
                    <a href="{{ route('stocks.materials') }}" style="text-decoration: none; color: inherit;">
                        <i class="fas fa-boxes"></i>
                        <h6>Stok</h6>
                        <p>Manajemen stok gudang</p>
                    </a>
                </div>
                @endif

                @if(auth()->user()->role->name === 'admin')
                <div class="help-item">
                    <a href="{{ route('master-data.users') }}" style="text-decoration: none; color: inherit;">
                        <i class="fas fa-users"></i>
                        <h6>Master Data</h6>
                        <p>Kelola data master</p>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endauth

        <!-- Footer Information -->
        <div class="footer-info">
            <p>
                <i class="fas fa-code me-2"></i>
                <strong>{{ config('app.name', 'ProdCore') }}</strong> - Sistem Manajemen Produksi Brakepad
            </p>
            <p>
                <i class="fas fa-calendar me-2"></i>
                {{ now()->format('l, d F Y') }} | 
                <i class="fas fa-clock me-2"></i>
                {{ now()->format('H:i:s') }}
            </p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/gh/zuramai/mazer@docs/demo/assets/static/js/initTheme.js"></script>
    <script>
        // Auto redirect after 30 seconds
        let countdown = 30;
        const redirectTimer = setInterval(() => {
            countdown--;
            if (countdown <= 0) {
                clearInterval(redirectTimer);
                window.location.href = '{{ route("dashboard") }}';
            }
        }, 1000);

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Press 'H' for Home
            if (e.key.toLowerCase() === 'h') {
                window.location.href = '{{ route("dashboard") }}';
            }
            
            // Press 'B' for Back
            if (e.key.toLowerCase() === 'b') {
                history.back();
            }
            
            // Press 'R' for Refresh
            if (e.key.toLowerCase() === 'r') {
                location.reload();
            }
        });

        // Log 404 error for analytics
        fetch('/api/log-activity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                action: '404_error',
                page: '{{ request()->fullUrl() }}',
                user_agent: navigator.userAgent,
                referrer: document.referrer,
                timestamp: new Date().toISOString()
            })
        }).catch(error => console.log('Log error:', error));

        // Console message for developers
        console.log(`
🔍 404 Error Debug Info:
📍 URL: {{ request()->fullUrl() }}
📅 Time: {{ now()->toISOString() }}
👤 User: {{ auth()->check() ? auth()->user()->name : 'Guest' }}
🌐 User Agent: ${navigator.userAgent}
📋 Referrer: ${document.referrer}

💡 Quick fixes:
- Check if route exists in routes/web.php
- Verify middleware permissions
- Check for typos in URL
- Ensure assets path are correct
        `);
    </script>
</body>
</html>