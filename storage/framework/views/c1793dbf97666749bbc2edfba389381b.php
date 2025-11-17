<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($title ?? config('app.name')); ?></title>

    <?php echo $__env->make('components.partials.seo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        
    <!-- Custom Styles -->
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <?php if(auth()->guard()->check()): ?>
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('dashboard.navbar', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3214918978-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    <?php endif; ?>
    
    <?php if(auth()->guard()->guest()): ?>
        <?php if(request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('forgot-password')): ?>
            <div class="auth-container">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('auth.auth-extra', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-3214918978-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                <?php echo e($slot); ?>

            </div>
        <?php else: ?>
            <?php echo e($slot); ?>

        <?php endif; ?>
    <?php else: ?>
        <?php echo e($slot); ?>

    <?php endif; ?>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scriptConfig(); ?> 
</body>
</html><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/components/layouts/app.blade.php ENDPATH**/ ?>