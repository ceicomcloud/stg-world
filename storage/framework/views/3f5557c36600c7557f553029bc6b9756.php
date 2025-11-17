<?php (
    $appName = config('app.name')
); ?>
<?php (
    $appUrl = rtrim(config('app.url') ?? url('/'), '/')
); ?>
<?php (
    $canonicalUrl = url()->current()
); ?>
<?php (
    $metaTitle = ($title ?? $metaTitle ?? $appName)
); ?>
<?php (
    $defaultDescription = 'World Of Stargate — MMO spatial en ligne. Rejoignez l\'aventure sur STG-WORLD.'
); ?>
<?php (
    $metaDescription = ($metaDescription ?? $defaultDescription)
); ?>
<?php (
    $metaImage = ($metaImage ?? asset('images/logo.png'))
); ?>
<?php (
    $metaRobots = ($metaRobots ?? 'index,follow')
); ?>
<?php (
    $googleVerification = env('GOOGLE_SITE_VERIFICATION')
); ?>
<?php (
    $bingVerification = env('BING_SITE_VERIFICATION')
); ?>

<link rel="canonical" href="<?php echo e($canonicalUrl); ?>" />
<meta name="description" content="<?php echo e($metaDescription); ?>" />
<meta name="robots" content="<?php echo e($metaRobots); ?>" />

<?php if(!empty($googleVerification)): ?>
    <meta name="google-site-verification" content="<?php echo e($googleVerification); ?>" />
<?php endif; ?>
<?php if(!empty($bingVerification)): ?>
    <meta name="msvalidate01" content="<?php echo e($bingVerification); ?>" />
<?php endif; ?>

<!-- Open Graph -->
<meta property="og:type" content="website" />
<meta property="og:site_name" content="<?php echo e($appName); ?>" />
<meta property="og:title" content="<?php echo e($metaTitle); ?>" />
<meta property="og:description" content="<?php echo e($metaDescription); ?>" />
<meta property="og:url" content="<?php echo e($canonicalUrl); ?>" />
<meta property="og:image" content="<?php echo e($metaImage); ?>" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="<?php echo e($metaTitle); ?>" />
<meta name="twitter:description" content="<?php echo e($metaDescription); ?>" />
<meta name="twitter:image" content="<?php echo e($metaImage); ?>" />

<!-- JSON-LD: Organization & Website -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "<?php echo e($appName); ?>",
  "url": "<?php echo e($appUrl); ?>",
  "logo": "<?php echo e(asset('images/logo.png')); ?>",
  "sameAs": [
    "https://discord.gg/UpBp2x6VPV"
  ]
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "<?php echo e($appName); ?>",
  "url": "<?php echo e($appUrl); ?>",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "<?php echo e($appUrl); ?>/search?query={query}",
    "query-input": "required name=query"
  }
}
</script><?php /**PATH /var/www/vhosts/terranovarp.xyz/stg-world.fr/resources/views/components/partials/seo.blade.php ENDPATH**/ ?>