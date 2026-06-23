<?php
declare(strict_types=1);
/** @var list<array{level: string, message: string}>|null $production_issues */
$productionIssues = $production_issues ?? [];
if ($productionIssues === []) {
    return;
}
?>
<div class="mb-4 space-y-2">
    <?php foreach ($productionIssues as $issue): ?>
        <?php
        $isError = ($issue['level'] ?? '') === 'error';
        $box = $isError
            ? 'border-red-200 bg-red-50 text-red-900'
            : 'border-amber-200 bg-amber-50 text-amber-950';
        $label = $isError ? 'Produção' : 'Atenção';
        ?>
        <p class="rounded-xl border px-4 py-2 text-sm <?= $box ?>">
            <span class="font-semibold"><?= htmlspecialchars($label) ?>:</span>
            <?= htmlspecialchars((string) ($issue['message'] ?? '')) ?>
        </p>
    <?php endforeach; ?>
</div>
