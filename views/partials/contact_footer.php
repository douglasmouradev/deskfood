<?php
declare(strict_types=1);
/** @var array<string,mixed> $config */
$em = trim((string) ($config['commercial_email'] ?? ''));
$telHref = trim((string) ($config['commercial_phone_tel'] ?? ''));
$telLabel = trim((string) ($config['commercial_phone_label'] ?? ''));
$company = trim((string) ($config['commercial_company'] ?? ''));
if ($em === '' && $telHref === '') {
    return;
}
?>
<div>
    <p class="text-xs font-bold uppercase tracking-wider text-ink-400">Contato<?= $company !== '' ? ' — ' . htmlspecialchars($company) : '' ?></p>
    <?php if ($em !== ''): ?>
        <p class="mt-2 text-sm text-ink-700">
            <a class="font-medium text-brand-600 hover:underline" href="mailto:<?= htmlspecialchars($em) ?>"><?= htmlspecialchars($em) ?></a>
        </p>
    <?php endif; ?>
    <?php if ($telHref !== '' && $telLabel !== ''): ?>
        <p class="mt-1 text-sm text-ink-700">
            <a class="font-medium text-brand-600 hover:underline" href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $telHref)) ?>"><?= htmlspecialchars($telLabel) ?></a>
        </p>
    <?php endif; ?>
</div>
