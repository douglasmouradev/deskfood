<?php
declare(strict_types=1);
use App\Helpers\Assets;
?>
<?php if (Assets::useTailwindCdn()): ?>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                brand: { 50: '#fff4ed', 100: '#ffe4d4', 500: '#f97316', 600: '#ea580c', 700: '#c2410c', 900: '#7c2d12' },
                ink: { 900: '#0f172a', 700: '#334155', 500: '#64748b' }
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                display: ['"Space Grotesk"', 'system-ui', 'sans-serif']
            }
        }
    }
};
</script>
<?php else: ?>
<link rel="stylesheet" href="<?= htmlspecialchars(Assets::tailwindStylesheetHref()) ?>">
<?php endif; ?>
<link rel="stylesheet" href="<?= htmlspecialchars(Assets::appStylesheetHref()) ?>">
