<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="description" content="Administration <?php echo e(config('app.name')); ?> - Interface de gestion">
    <meta name="robots" content="noindex, nofollow">

    <title><?php echo e($title ?? 'Administration'); ?> - <?php echo e(config('app.name')); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/admin.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
    
    <!-- Custom CSS Variables -->
    <style>
        :root {
            --admin-page-title: '<?php echo e($title ?? "Administration"); ?>';
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
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('admin.widget.menu', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-1807318471-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        
        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Flash Messages -->
            <?php if(session('success')): ?>
                <div class="admin-alert admin-alert-success admin-slide-in">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo e(session('success')); ?></span>
                    <button class="admin-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if(session('error')): ?>
                <div class="admin-alert admin-alert-danger admin-slide-in">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo e(session('error')); ?></span>
                    <button class="admin-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if(session('warning')): ?>
                <div class="admin-alert admin-alert-warning admin-slide-in">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo e(session('warning')); ?></span>
                    <button class="admin-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if(session('info')): ?>
                <div class="admin-alert admin-alert-info admin-slide-in">
                    <i class="fas fa-info-circle"></i>
                    <span><?php echo e(session('info')); ?></span>
                    <button class="admin-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <!-- Page Content -->
            <div class="admin-content">
                <?php echo e($slot); ?>

            </div>
            
            <!-- Footer -->
            <footer class="admin-footer">
                <div class="admin-footer-content">
                    <div class="admin-footer-left">
                        <span class="admin-footer-text">
                            © <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?> Administration
                        </span>
                    </div>
                    
                    <div class="admin-footer-right">
                        <span class="admin-footer-version">
                            Version 3.0.0
                        </span>
                        <span class="admin-footer-separator">•</span>
                        <span class="admin-footer-time" id="currentTime">
                            <?php echo e(now()->format('H:i:s')); ?>

                        </span>
                    </div>
                </div>
            </footer>
        </main>
    </div>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scriptConfig(); ?>


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
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/components/layouts/admin.blade.php ENDPATH**/ ?>