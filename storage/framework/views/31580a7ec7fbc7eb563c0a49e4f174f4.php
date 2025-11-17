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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    <?php ($isMobile = \App\Support\Device::isMobile()); ?>
    <?php echo app('Illuminate\Foundation\Vite')($isMobile 
        ? ['resources/css/app.css', 'resources/css/mobile.css', 'resources/js/app.js'] 
        : ['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="game-body">
    <div class="game-layout" x-data="{ drawerOpen: false }" x-on:toggle-drawer.window="drawerOpen = !drawerOpen" x-effect="document.documentElement.classList.toggle('no-scroll', drawerOpen); document.body.classList.toggle('no-scroll', drawerOpen)">
        <header class="game-header">
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('game.navbar', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-907921839-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        </header>

        <main id="game-content" class="game-main" role="main">
            <section class="game-content" aria-label="Contenu de la page">
                <?php echo e($slot); ?>

            </section>
        </main>

        <!-- Overlay et tiroir latéral des ressources -->
        <div class="drawer-overlay" style="display: none;" x-show="drawerOpen" x-transition.opacity x-on:click="drawerOpen = false" aria-hidden="true"></div>
        <aside class="game-drawer left" style="display: none;" aria-label="Ressources" x-show="drawerOpen" x-transition>
            <div class="drawer-header">
                <h2>Ressources</h2>
                <button class="drawer-close" aria-label="Fermer" x-on:click="drawerOpen = false"><i class="fas fa-times"></i></button>
            </div>
            <div class="drawer-body">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('game.resource', []);

$__html = app('livewire')->mount($__name, $__params, 'lw-907921839-1', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </div>
        </aside>

        <div class="daily-quests-icon" onclick="Livewire.dispatch('openModal', { component: 'game.modal.daily-quests', arguments: { title: 'Quêtes journalières' } })">
            <i class="fas fa-list-check"></i>
        </div>
    </div>

    <!-- Modal System -->
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('wire-elements-modal');

$__html = app('livewire')->mount($__name, $__params, 'lw-907921839-2', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scriptConfig(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/components/layouts/game.blade.php ENDPATH**/ ?>