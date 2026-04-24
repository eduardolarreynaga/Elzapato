<?php
$pageTitle = $pageTitle ?? 'Panel Admin';
$activeMenu = $activeMenu ?? '';
$pageStyles = $pageStyles ?? [];

$globalLowStockThreshold = defined('LOW_STOCK_THRESHOLD') ? (int)LOW_STOCK_THRESHOLD : 10;
$globalLowStockItems = [];
$globalLowStockSignature = '';
$showGlobalLowStockAlert = isset($_SESSION['auth']) && $_SESSION['auth'] === true;

if (!empty($_SESSION['suppress_low_stock_alert_once'])) {
    $showGlobalLowStockAlert = false;
    unset($_SESSION['suppress_low_stock_alert_once']);
}

$conexionPath = __DIR__ . '/../../../model/conexion.php';
if ($showGlobalLowStockAlert && file_exists($conexionPath)) {
    require_once $conexionPath;

    if (class_exists('Conexion')) {
        try {
            $dbGlobalAlerts = Conexion::conectar();
            $stmtGlobalAlerts = $dbGlobalAlerts->prepare("SELECT v.id_variante, p.nombre_producto, v.talla, v.color, v.stock FROM producto_variante v INNER JOIN productos p ON p.id_producto = v.id_producto WHERE v.estado = 'activo' AND v.stock > 0 AND v.stock <= :umbral ORDER BY v.id_variante ASC");
            $stmtGlobalAlerts->bindParam(':umbral', $globalLowStockThreshold, PDO::PARAM_INT);
            $stmtGlobalAlerts->execute();
            $globalLowStockAll = $stmtGlobalAlerts->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (!empty($globalLowStockAll)) {
                $signatureData = array_map(static function ($row) {
                    return [
                        'id' => (int)($row['id_variante'] ?? 0),
                        'stock' => (int)($row['stock'] ?? 0)
                    ];
                }, $globalLowStockAll);

                $globalLowStockSignature = sha1(json_encode($signatureData));
                $lastSeenSignature = $_SESSION['low_stock_alert_signature_seen'] ?? '';

                if ($globalLowStockSignature !== $lastSeenSignature) {
                    $globalLowStockItems = array_slice($globalLowStockAll, 0, 8);
                    $_SESSION['low_stock_alert_signature_seen'] = $globalLowStockSignature;
                }
            } else {
                $_SESSION['low_stock_alert_signature_seen'] = '';
            }
        } catch (Throwable $e) {
            $globalLowStockItems = [];
        }
    }
}
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
    <link rel="stylesheet" href="/ElZapato/Assets/css/components/buttons.css?v=20260424">
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

        .global-stock-alert {
            position: fixed;
            top: 18px;
            right: 22px;
            z-index: 12000;
            width: min(420px, 94vw);
            background: #fff8f3;
            border: 1px solid #e8c9b1;
            border-left: 5px solid #bc6e32;
            border-radius: 12px;
            box-shadow: 0 10px 24px rgba(0,0,0,.12);
            padding: 12px 14px;
            font-family: inherit;
        }

        .global-stock-alert h4 {
            margin: 0 0 8px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #772C24;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .global-stock-alert ul {
            margin: 0;
            padding-left: 18px;
            max-height: 180px;
            overflow: auto;
        }

        .global-stock-alert li {
            font-size: 0.95rem;
            color: #4D3B2E;
            margin-bottom: 4px;
        }

        .global-stock-alert .alert-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            gap: 8px;
        }

        .global-stock-alert .alert-link {
            color: #772C24;
            font-weight: 600;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .global-stock-alert .alert-close {
            border: none;
            background: #AB886D;
            color: #fff;
            border-radius: 7px;
            padding: 6px 12px;
            cursor: pointer;
            font-size: 0.9rem;
        }
    </style>

    <link rel="icon" type="image/x-icon" href="/ElZapato/Assets/img/logo.png">
</head>
<body class="dashboard-body">
    <?php if ($showGlobalLowStockAlert && !empty($globalLowStockItems)): ?>
    <div class="global-stock-alert" id="globalStockAlert">
        <h4><i class="fas fa-exclamation-triangle"></i> Alerta de stock bajo</h4>
        <ul>
            <?php foreach ($globalLowStockItems as $item): ?>
                <li>
                    <?= htmlspecialchars($item['nombre_producto'] ?? '') ?>
                    <?php if (!empty($item['talla'])): ?> | Talla <?= htmlspecialchars($item['talla']) ?><?php endif; ?>
                    <?php if (!empty($item['color'])): ?> | <?= htmlspecialchars($item['color']) ?><?php endif; ?>
                    → <strong><?= (int)($item['stock'] ?? 0) ?></strong>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="alert-actions">
            <a class="alert-link" href="/ElZapato/src/views/admin/productos.php">Ver inventario</a>
            <button type="button" class="alert-close" onclick="document.getElementById('globalStockAlert')?.remove()">Cerrar</button>
        </div>
    </div>
    <?php endif; ?>
    <div class="dashboard">
        <?php require __DIR__ . '/admin-sidebar.php'; ?>
        <main class="main-content">
