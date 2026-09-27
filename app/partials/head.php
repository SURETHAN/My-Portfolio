<?php if (!defined('APP_BOOT')) { http_response_code(403); exit; } ?>
<!doctype html>
<html lang="en" class="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(SITE_TITLE) ?></title>
<meta name="description" content="<?= e(SITE_DESC) ?>">
<meta name="author" content="<?= e(SITE_NAME) ?>">
<meta name="theme-color" content="#0a0a0b">
<link rel="canonical" href="<?= e(SITE_URL) ?>/">
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="<?= e(asset('assets/images/favicon.svg')) ?>" type="image/svg+xml">
<link rel="icon" href="<?= e(asset('assets/images/favicon-48x48.png')) ?>" type="image/png" sizes="48x48">
<link rel="icon" href="<?= e(asset('assets/images/favicon-32x32.png')) ?>" type="image/png" sizes="32x32">
<link rel="icon" href="<?= e(asset('assets/images/favicon-16x16.png')) ?>" type="image/png" sizes="16x16">
<link rel="icon" href="<?= e(asset('assets/images/favicon-192x192.png')) ?>" type="image/png" sizes="192x192">
<link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">

<meta property="og:type" content="website">
<meta property="og:url" content="<?= e(SITE_URL) ?>/">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:title" content="<?= e(SITE_TITLE) ?>">
<meta property="og:description" content="<?= e(SITE_DESC) ?>">
<meta property="og:image" content="<?= e(SITE_URL) ?>/assets/images/og.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e(SITE_TITLE) ?>">
<meta name="twitter:description" content="<?= e(SITE_DESC) ?>">
<meta name="twitter:image" content="<?= e(SITE_URL) ?>/assets/images/og.png">

<?php /* Theme boot: apply saved theme before first paint (no FOUC) */ ?>
<script nonce="<?= e($nonce) ?>">
(function () {
  var d = document.documentElement, q = location.search;
  /* Dark is the default showpiece; only an explicit saved choice flips it */
  if (q.indexOf('theme=light') !== -1) { d.classList.remove('dark'); }
  else try {
    if (localStorage.getItem('theme') === 'light') d.classList.remove('dark');
  } catch (e) {}
  /* ?noanim=1: static render (testing/screenshots). Otherwise enhance-only
     reveal states; if the app module never runs, unhide everything. */
  window.__noanim = q.indexOf('noanim') !== -1;
  if (!window.__noanim) {
    d.classList.add('js');
    setTimeout(function () { if (!window.__appReady) d.classList.remove('js'); }, 3000);
  }
})();
</script>

<?php /* Bare paths (no ?v=) — must byte-match the URLs inside fonts.css
         or the browser treats them as different resources and double-loads */ ?>
<link rel="preload" href="/assets/fonts/space-grotesk-3.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/inter-0.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/jetbrains-mono-1.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('assets/css/fonts.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/main.css')) ?>">

<script type="importmap" nonce="<?= e($nonce) ?>">
{ "imports": { "three": "<?= e(asset('assets/vendor/three.module.min.js')) ?>" } }
</script>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'WebSite',
    'name'     => SITE_NAME,
    'alternateName' => 'Surethan',
    'url'      => SITE_URL . '/',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'Person',
    'name'     => SITE_NAME,
    'alternateName' => ['Surethan', 'Surethan S'],
    'jobTitle' => 'Product Developer',
    'worksFor' => ['@type' => 'Organization', 'name' => $identity['company']],
    'email'    => 'mailto:' . $identity['email'],
    'url'      => SITE_URL,
    'sameAs'   => array_values(array_filter([$identity['linkedin'], $identity['github']])),
    'address'  => ['@type' => 'PostalAddress', 'addressLocality' => 'Bengaluru', 'addressCountry' => 'IN'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
</head>
<body class="grain">
<a class="skip-link" href="#main">Skip to content</a>
