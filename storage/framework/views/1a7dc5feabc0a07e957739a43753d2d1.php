<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Paiement réussi']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Paiement réussi')]); ?>
    <div class="paypal-page" style="max-width: 720px; margin: 40px auto; padding: 24px;">
        <div class="paypal-card" style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; text-align: center; background: #ffffff;">
            <div style="font-size: 48px; color: #16a34a; margin-bottom: 12px;">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
            </div>
            <h1 style="font-size: 24px; margin-bottom: 8px;">Paiement réussi</h1>
            <p style="color: #6b7280; margin-bottom: 20px;">Votre paiement PayPal a été validé. Vous pouvez retourner au Commerce.</p>
            <a href="<?php echo e(route('game.trade')); ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #2563eb; color: #fff; padding: 10px 16px; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-exchange-alt" aria-hidden="true"></i>
                Retour au Commerce
            </a>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/paypal/success.blade.php ENDPATH**/ ?>