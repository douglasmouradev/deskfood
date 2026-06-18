<?php
declare(strict_types=1);
/** @var string|null $headTitle */
/** @var bool|null $headAlpine */
/** @var bool|null $headAnalytics */
/** @var string|null $headRobots */
/** @var string|null $metaDescription */
/** @var string|null $canonicalPath */
$config = require BASE_PATH . '/config/app.php';
$headTitle = $headTitle ?? ($title ?? ($config['name'] ?? 'Desk Food'));
$headAlpine = (bool) ($headAlpine ?? false);
$headAnalytics = (bool) ($headAnalytics ?? false);
$headRobots = $headRobots ?? null;
$metaDescription = $metaDescription ?? '';
$canonicalPath = $canonicalPath ?? null;
$baseUrl = (string) ($config['url'] ?? '');
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ($headRobots !== null): ?>
<meta name="robots" content="<?= htmlspecialchars($headRobots) ?>">
<?php endif; ?>
<?php if ($metaDescription !== ''): ?>
<meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
<?php endif; ?>
<?php if ($canonicalPath !== null && $canonicalPath !== ''): ?>
<link rel="canonical" href="<?= htmlspecialchars($baseUrl . $canonicalPath) ?>">
<?php endif; ?>
<meta name="theme-color" content="#18181b">
<title><?= htmlspecialchars($headTitle) ?></title>
<link rel="icon" href="/assets/img/logo.png" type="image/png">
<link rel="apple-touch-icon" href="/assets/img/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600&display=swap" rel="stylesheet">
<?php require BASE_PATH . '/views/partials/tailwind_assets.php'; ?>
<?php if ($headAlpine): ?>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
<?php endif; ?>
<?php if ($headAnalytics): ?>
<?php require BASE_PATH . '/views/partials/analytics.php'; ?>
<?php endif; ?>
