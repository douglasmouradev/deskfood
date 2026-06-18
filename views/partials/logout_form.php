<?php
declare(strict_types=1);
/** @var string $action */
/** @var string $label */
/** @var string $class */
/** @var string $buttonClass */
$label = $label ?? 'Sair';
$class = $class ?? 'inline';
$buttonClass = $buttonClass ?? 'w-full text-left';
?>
<form method="post" action="<?= htmlspecialchars($action) ?>" class="<?= htmlspecialchars($class) ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars(\App\Helpers\Csrf::token()) ?>">
    <button type="submit" class="<?= htmlspecialchars($buttonClass) ?>"><?= htmlspecialchars($label) ?></button>
</form>
