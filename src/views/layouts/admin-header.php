<?php
$pageHeading = $pageHeading ?? 'Dashboard';
$searchInputId = $searchInputId ?? '';
$searchPlaceholder = $searchPlaceholder ?? 'Buscar...';
$showSearch = $showSearch ?? true;
?>
<header class="dashboard-header">
    <div class="header-left">
        <button class="menu-toggle" type="button" aria-label="Abrir menú lateral">
            <i class="fas fa-bars"></i>
        </button>
        <h1><?= htmlspecialchars($pageHeading, ENT_QUOTES, 'UTF-8') ?></h1>
    </div>
    <div class="header-right">
        <?php if ($showSearch): ?>
            <div class="header-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="<?= htmlspecialchars($searchPlaceholder, ENT_QUOTES, 'UTF-8') ?>"<?= $searchInputId !== '' ? ' id="' . htmlspecialchars($searchInputId, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
            </div>
        <?php endif; ?>
        <div class="header-date">
            <i class="fas fa-calendar-alt"></i>
            <span id="current-date"></span>
        </div>
    </div>
</header>
