<?php
declare(strict_types=1);
$config = require BASE_PATH . '/config/app.php';
$ga = trim((string) ($config['analytics_ga_id'] ?? ''));
if ($ga === '') {
    return;
}
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga) ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', <?= json_encode($ga, JSON_THROW_ON_ERROR) ?>);
</script>
