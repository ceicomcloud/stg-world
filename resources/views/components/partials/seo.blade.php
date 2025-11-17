@php(
    $appName = config('app.name')
)
@php(
    $appUrl = rtrim(config('app.url') ?? url('/'), '/')
)
@php(
    $canonicalUrl = url()->current()
)
@php(
    $metaTitle = ($title ?? $metaTitle ?? $appName)
)
@php(
    $defaultDescription = 'World Of Stargate — MMO spatial en ligne. Rejoignez l\'aventure sur STG-WORLD.'
)
@php(
    $metaDescription = ($metaDescription ?? $defaultDescription)
)
@php(
    $metaImage = ($metaImage ?? asset('images/logo.png'))
)
@php(
    $metaRobots = ($metaRobots ?? 'index,follow')
)
@php(
    $googleVerification = env('GOOGLE_SITE_VERIFICATION')
)
@php(
    $bingVerification = env('BING_SITE_VERIFICATION')
)

<link rel="canonical" href="{{ $canonicalUrl }}" />
<meta name="description" content="{{ $metaDescription }}" />
<meta name="robots" content="{{ $metaRobots }}" />

@if(!empty($googleVerification))
    <meta name="google-site-verification" content="{{ $googleVerification }}" />
@endif
@if(!empty($bingVerification))
    <meta name="msvalidate01" content="{{ $bingVerification }}" />
@endif

<!-- Open Graph -->
<meta property="og:type" content="website" />
<meta property="og:site_name" content="{{ $appName }}" />
<meta property="og:title" content="{{ $metaTitle }}" />
<meta property="og:description" content="{{ $metaDescription }}" />
<meta property="og:url" content="{{ $canonicalUrl }}" />
<meta property="og:image" content="{{ $metaImage }}" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $metaTitle }}" />
<meta name="twitter:description" content="{{ $metaDescription }}" />
<meta name="twitter:image" content="{{ $metaImage }}" />

<!-- JSON-LD: Organization & Website -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "{{ $appName }}",
  "url": "{{ $appUrl }}",
  "logo": "{{ asset('images/logo.png') }}",
  "sameAs": [
    "https://discord.gg/UpBp2x6VPV"
  ]
}
</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "{{ $appName }}",
  "url": "{{ $appUrl }}",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "{{ $appUrl }}/search?query={query}",
    "query-input": "required name=query"
  }
}
</script>