<?php
require_once __DIR__ . '/../../config/auth.php';

if (!is_authenticated()) {
    redirect_to('index.php');
}

$currentRole = get_current_user_role();
if (!in_array($currentRole, ['seller', 'admin'], true)) {
    redirect_to('index.php');
}

// 1. CARGAR DATOS DE LA BASE DE DATOS
require_once "../../../controller/productosController.php";
require_once "../../../model/ProductosModel.php";
require_once "../../../controller/categoriasController.php";
require_once "../../../model/CategoriasModel.php";

$productos = ProductosController::ctrMostrarProductos();
$categorias = CategoriasController::ctrMostrarCategorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Zapatería El Zapato</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ElZapato/Assets/css/styles.css?v=20260323">
    
    <style>
        /* Mantenemos tus estilos originales intactos */
        .menu-items ul {
            grid-template-columns: repeat(auto-fill, minmax(118px, 1fr)) !important;
            gap: 10px !important;
        }

        .menu-items li.product-item {
            position: relative !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: flex-end !important;
            overflow: hidden !important;
            padding: 0 !important;
            min-height: 91px !important;
            /* La imagen de fondo ahora es dinámica, aquí dejamos el fallback */
            background: linear-gradient(to top, rgba(0,0,0,0.45), rgba(0,0,0,0.05)), url('/ElZapato/Assets/img/zapa.jpeg') center/cover no-repeat !important;
            cursor: pointer;
            transition: transform 0.1s;
        }

        .menu-items li.product-item:active { transform: scale(0.95); }

        .menu-items li.product-item::after {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: 0;
            height: 29px;
            background: rgba(255, 255, 255, 0.72);
            z-index: 1;
        }

        .menu-items li.product-item .item {
            position: absolute;
            left: 8px; bottom: 6px;
            z-index: 2;
            margin: 0 !important;
            max-width: calc(100% - 60px);
            font-size: 0.78rem;
            font-weight: 700;
            color: #111 !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .menu-items li.product-item .category { display: none !important; }

        .menu-items li.product-item .price {
            position: absolute;
            right: 8px; bottom: 6px;
            z-index: 2;
            margin: 0 !important;
            font-size: 0.76rem;
            font-weight: 700;
            color: #111 !important;
        }
    </style>
</head>
<body class="keyboard-hidden">
    <div class="register">
        <div class="left">
            <div class="order-window">
                <table id="ticketTable">
                    <thead>
                        <tr>
                            <th>Cant.</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>

            <div class="order-total">
                <span id="totalDisplay">Total: $0.00</span>
            </div>

            <div class="buttons">
                <button class="action-btn" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                <button class="num-btn">1</button>
                <button class="num-btn">2</button>
                <button class="num-btn">3</button>
                <button class="action-btn" onclick="resetVenta()"><i class="fa fa-times"></i> Reiniciar</button>
                <button class="num-btn">4</button>
                <button class="num-btn">5</button>
                <button class="num-btn">6</button>
                <button class="action-btn">.00</button>
                <button class="num-btn">7</button>
                <button class="num-btn">8</button>
                <button class="num-btn">9</button>
                <button class="action-btn exit-btn"><i class="fas fa-ban"></i> Anular</button>
                <button class="num-btn">0</button>
                <button class="action-btn"><i class="fa fa-minus"></i></button>
                <button class="action-btn"><i class="fa fa-plus"></i></button>
            </div>

            <div class="left-keys">
                <ul>
                    <li onclick="window.location.href='/ElZapato/index.php'"><i class="fas fa-sign-out-alt"></i><span>Salir</span></li>
                    <li class="keyboard-toggle active" data-toggle-keyboard><i class="fas fa-keyboard"></i></li>
                    <li onclick="window.print()"><i class="fas fa-print"></i><span>Imprimir</span></li>
                </ul>
            </div>
        </div>

        <div class="right">
            <div class="categories">
                <ul id="categoryFilters">
                    <li><a href="#" class="active" data-filter="all">Todos</a></li>
                    <?php foreach ($categorias as $cat): ?>
                        <li><a href="#" data-filter="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nombre_categoria']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="menu-items">
                <ul id="productsGrid">
                    <?php foreach ($productos as $p): 
                        // --- LÓGICA DE IMAGEN DINÁMICA POR ID ---
                        $id = $p['id_producto'];
                        $imagenPath = '/ElZapato/Assets/img/zapa.jpeg'; // Imagen por defecto
                        
                        // Formatos que el sistema soporta
                        $formatos = ['jpg', 'jpeg', 'png', 'webp'];
                        
                        foreach ($formatos as $ext) {
                            // Buscamos si el archivo existe físicamente
                            $archivoFisico = __DIR__ . "/../../../Assets/img/productos/" . $id . "." . $ext;
                            
                            if (file_exists($archivoFisico)) {
                                $imagenPath = "/ElZapato/Assets/img/productos/" . $id . "." . $ext;
                                break; // Si la encuentra, deja de buscar
                            }
                        }
                        // ----------------------------------------
                    ?>
                    <li class="product-item" 
                        data-id="<?= $id ?>"
                        data-category="<?= $p['id_categoria'] ?>" 
                        data-price="<?= $p['precio_venta'] ?>"
                        style="background: linear-gradient(to top, rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.05)), url('<?= $imagenPath ?>?v=<?= time() ?>') center/cover no-repeat !important;">
                        
                        <span class="item"><?= htmlspecialchars($p['nombre_producto']) ?></span>
                        <span class="category"><?= $p['id_categoria'] ?></span>
                        <span class="price">$<?= number_format($p['precio_venta'], 2) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="payment-keys">
                <ul>
                    <li class="payment-method" data-method="efectivo"><i class="fas fa-money-bill-alt fa-2x"></i><span>Efectivo</span></li>
                    <li class="payment-method" data-method="tarjeta"><i class="fas fa-credit-card fa-2x"></i><span>Tarjeta</span></li>
                    <li class="payment-method" data-method="transferencia"><i class="fas fa-exchange-alt fa-2x"></i><span>Transf.</span></li>
                    <li class="payment-method" data-method="gift"><i class="fas fa-gift fa-2x"></i><span>Gift Card</span></li>
                    <li class="payment-method" data-method="empleado"><i class="fas fa-user fa-2x"></i><span>Empleado</span></li>
                </ul>
            </div>
        </div>
    </div>

    <script src="/ElZapato/Assets/js/script.js?v=999" defer></script>
    <script>
        // Lógica de filtrado por categoría
        document.querySelectorAll('#categoryFilters a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('#categoryFilters a').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                
                const filter = link.getAttribute('data-filter');
                document.querySelectorAll('.product-item').forEach(item => {
                    if(filter === 'all' || item.getAttribute('data-category') === filter) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>