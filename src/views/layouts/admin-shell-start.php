<?php
$pageTitle = $pageTitle ?? 'Panel Admin';
$activeMenu = $activeMenu ?? '';
$pageStyles = $pageStyles ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> - Zapatería El Zapato</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/ElZapato/Assets/css/styles.css">
    <link rel="stylesheet" href="/ElZapato/Assets/css/base/variables.css">
    <link rel="stylesheet" href="/ElZapato/Assets/css/base/reset.css">
    <link rel="stylesheet" href="/ElZapato/Assets/css/layout/dashboard.css">
    <link rel="stylesheet" href="/ElZapato/Assets/css/layout/sidebar.css">
    <link rel="stylesheet" href="/ElZapato/Assets/css/components/cards.css">
    <link rel="stylesheet" href="/ElZapato/Assets/css/components/buttons.css">
    <link rel="stylesheet" href="/ElZapato/Assets/css/components/forms.css">
    <link rel="stylesheet" href="/ElZapato/Assets/css/components/tables.css">
    <link rel="stylesheet" href="/ElZapato/Assets/css/components/footer.css">
    <link rel="stylesheet" href="/ElZapato/Assets/css/components/dev-modal.css?v=20260423">
    <?php foreach ($pageStyles as $stylePath): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($stylePath, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>

    <style>
        .dashboard .stats-list-item {
            background: transparent !important;
        }
    </style>

    <link rel="icon" type="image/x-icon" href="/ElZapato/Assets/img/logo.png">
</head>
<body class="dashboard-body">
    <div class="dashboard">
        <?php require __DIR__ . '/admin-sidebar.php'; ?>
        <main class="main-content">
