<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Administration {{ config('app.name') }} - Interface de gestion">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? 'Administration' }} - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Styles -->
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
    @stack('styles')
    
    <!-- Custom CSS Variables -->
    <style>
        :root {
            --admin-page-title: '{{ $title ?? "Administration" }}';
        }
    </style>
</head>
<body class="admin-layout">
    <!-- Loading Overlay -->
    <div id="admin-loading" class="admin-loading-overlay" style="display: none;">
        <div class="admin-loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Chargement...</span>
        </div>
    </div>

    <!-- Main Container -->
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <livewire:admin.widget.menu />
        
        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="admin-alert admin-alert-success admin-slide-in">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button class="admin-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="admin-alert admin-alert-danger admin-slide-in">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button class="admin-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            
            @if(session('warning'))
                <div class="admin-alert admin-alert-warning admin-slide-in">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>{{ session('warning') }}</span>
                    <button class="admin-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            
            @if(session('info'))
                <div class="admin-alert admin-alert-info admin-slide-in">
                    <i class="fas fa-info-circle"></i>
                    <span>{{ session('info') }}</span>
                    <button class="admin-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            
            <!-- Page Content -->
            <div class="admin-content">
                {{ $slot }}
            </div>
            
            <!-- Footer -->
            <footer class="admin-footer">
                <div class="admin-footer-content">
                    <div class="admin-footer-left">
                        <span class="admin-footer-text">
                            © {{ date('Y') }} {{ config('app.name') }} Administration
                        </span>
                    </div>
                    
                    <div class="admin-footer-right">
                        <span class="admin-footer-version">
                            Version 3.0.0
                        </span>
                        <span class="admin-footer-separator">•</span>
                        <span class="admin-footer-time" id="currentTime">
                            {{ now()->format('H:i:s') }}
                        </span>
                    </div>
                </div>
            </footer>
        </main>
    </div>

    @livewireScriptConfig

    <!-- Scripts -->
    <script>
        // Update time every second
        function updateTime() {
            const timeElement = document.getElementById('currentTime');
            if (timeElement) {
                const now = new Date();
                timeElement.textContent = now.toLocaleTimeString('fr-FR');
            }
        }
        
        setInterval(updateTime, 1000);
        
        // Loading overlay functions
        function showLoading() {
            document.getElementById('admin-loading').style.display = 'flex';
        }
        
        function hideLoading() {
            document.getElementById('admin-loading').style.display = 'none';
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.admin-alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>