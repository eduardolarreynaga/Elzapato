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
        :root {
            --primary-light: #E4E0E1;   /* Fondo muy claro */
            --primary-soft: #D6C0B3;    /* Tono suave intermedio */
            --primary-dark: #AB886D;    /* Tono oscuro para detalles y éxito */
            --text-dark: #000000;       /* Negro solo para texto */
            --font-family: "Roboto", sans-serif;
            --nocolor: #772c24;         /* Rojo del sistema para deudas/alertas */
        }

        /* Estilos base de la interfaz */
        .menu-items ul { grid-template-columns: repeat(auto-fill, minmax(118px, 1fr)) !important; gap: 10px !important; }
        .menu-items li.product-item {
            position: relative !important; display: flex !important; flex-direction: column !important;
            justify-content: flex-end !important; overflow: hidden !important; padding: 0 !important;
            min-height: 91px !important; background: linear-gradient(to top, rgba(0,0,0,0.45), rgba(0,0,0,0.05)), url('/ElZapato/Assets/img/zapa.jpeg') center/cover no-repeat !important;
            cursor: pointer; transition: transform 0.1s;
        }
        .menu-items li.product-item:active { transform: scale(0.95); }
        .menu-items li.product-item::after { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 29px; background: rgba(255, 255, 255, 0.72); z-index: 1; }
        .menu-items li.product-item .item { position: absolute; left: 8px; bottom: 6px; z-index: 2; margin: 0 !important; max-width: calc(100% - 60px); font-size: 0.78rem; font-weight: 700; color: var(--text-dark) !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .menu-items li.product-item .price { position: absolute; right: 8px; bottom: 6px; z-index: 2; margin: 0 !important; font-size: 0.76rem; font-weight: 700; color: var(--text-dark) !important; }

        /* --- ESTILOS DE LOS MODALES --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7); display: none; 
            justify-content: center; align-items: center; z-index: 9999;
            backdrop-filter: blur(3px);
            /* Transición suave para el fondo también */
            transition: all 0.5s ease;
        }
        .modal-content {
            background: #fff; padding: 25px; border-radius: 12px; width: 90%;
            max-width: 420px; box-shadow: 0 15px 35px rgba(0,0,0,0.4); 
            border: 2px solid var(--primary-soft);
            
            /* --- NUEVA ANIMACIÓN MÁS DESPACIO Y AMIGABLE --- */
            /* Aumentamos duración a 0.6s y usamos cubic-bezier para desaceleración suave */
            animation: fadeInFriendly 0.6s cubic-bezier(0.23, 1, 0.32, 1); 
        }
        
        /* Definición de la animación amigable: entra suavemente desde abajo */
        @keyframes fadeInFriendly { 
            from { 
                opacity: 0; 
                transform: translateY(40px) scale(0.95); /* Empieza más abajo y pequeña */
            } 
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); /* Sube suavemente a su posición */
            } 
        }
        
        .modal-header { border-bottom: 2px solid var(--primary-light); margin-bottom: 20px; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h2 { margin: 0; font-size: 1.4rem; color: var(--primary-dark); font-weight: 700; }
        
        .modal-body { display: flex; flex-direction: column; gap: 15px; }
        
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-weight: 700; font-size: 0.9rem; color: var(--primary-dark); }
        .form-group input { 
            padding: 12px; border: 1px solid var(--primary-soft); border-radius: 8px; 
            font-size: 1.2rem; outline: none; background: var(--primary-light); color: var(--text-dark);
        }
        .form-group input:focus { border-color: var(--primary-dark); background: #fff; }

        .total-destacado { 
            background: var(--primary-dark); color: #fff; padding: 20px; 
            text-align: center; border-radius: 8px; font-size: 2.2rem; font-weight: 700; 
        }
        .vuelto-box { 
            background: var(--primary-light); color: var(--text-dark); padding: 15px; 
            text-align: center; border-radius: 8px; border: 2px solid var(--primary-soft); 
            font-size: 1.6rem; font-weight: 700; transition: all 0.3s ease;
        }
        .btn-confirmar { 
            background: var(--primary-dark); color: white; border: none; padding: 15px; 
            border-radius: 8px; font-size: 1.1rem; font-weight: 700; 
            cursor: pointer; transition: opacity 0.2s;
        }
        .btn-confirmar:hover { opacity: 0.9; }
        .close-modal { cursor: pointer; font-size: 1.8rem; color: var(--nocolor); font-weight: bold; }
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
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="order-total">
                <span id="totalDisplay">Total: $0.00</span>
            </div>

            <div class="buttons">
                <button class="action-btn"><i class="fa fa-plus"></i></button>
                <button class="num-btn">1</button><button class="num-btn">2</button><button class="num-btn">3</button>
                <button class="action-btn"><i class="fa fa-minus"></i></button>
                <button class="num-btn">4</button><button class="num-btn">5</button><button class="num-btn">6</button>
                <button class="action-btn" onclick="resetVenta()"><i class="fa fa-times"></i> Reiniciar</button>
                <button class="num-btn">7</button><button class="num-btn">8</button><button class="num-btn">9</button>
                <button class="action-btn exit-btn"><i class="fas fa-ban"></i> Anular</button>
                <button class="num-btn">0</button><button class="action-btn">.00</button><button class="action-btn"><i class="fa fa-equals"></i></button>
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
                        $id = $p['id_producto'];
                        $imagenPath = '/ElZapato/Assets/img/zapa.jpeg';
                        $formatos = ['jpg', 'jpeg', 'png', 'webp'];
                        foreach ($formatos as $ext) {
                            $archivoFisico = __DIR__ . "/../../../Assets/img/productos/" . $id . "." . $ext;
                            if (file_exists($archivoFisico)) {
                                $imagenPath = "/ElZapato/Assets/img/productos/" . $id . "." . $ext;
                                break;
                            }
                        }
                    ?>
                    <li class="product-item" data-id="<?= $id ?>" data-category="<?= $p['id_categoria'] ?>" data-price="<?= $p['precio_venta'] ?>"
                        style="background: linear-gradient(to top, rgba(0, 0, 0, 0.45), rgba(0, 0, 0, 0.05)), url('<?= $imagenPath ?>?v=<?= time() ?>') center/cover no-repeat !important;">
                        <span class="item"><?= htmlspecialchars($p['nombre_producto']) ?></span>
                        <span class="price">$<?= number_format($p['precio_venta'], 2) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="payment-keys">
                <ul>
                    <li class="payment-method" data-method="efectivo"><i class="fas fa-money-bill-alt fa-2x"></i><span>Efectivo</span></li>
                    <li class="payment-method" data-method="tarjeta"><i class="fas fa-credit-card fa-2x"></i><span>Tarjeta</span></li>
                    <li class="payment-method" data-method="empleado"><i class="fas fa-user fa-2x"></i><span>Empleado</span></li>
                </ul>
            </div>
        </div>
    </div>

    <div id="modalEfectivo" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-money-bill-wave"></i> Cobro en Efectivo</h2>
                <span class="close-modal" onclick="cerrarModales()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="total-destacado">
                    <small style="font-size: 0.8rem; display: block; opacity: 0.8; margin-bottom: 5px;">TOTAL A PAGAR:</small>
                    <span id="efectivoTotal">$0.00</span>
                </div>
                <div class="form-group">
                    <label>Efectivo Recibido</label>
                    <input type="number" id="montoRecibido" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="vuelto-box" id="vueltoContenedor">
                    <small id="vueltoEtiqueta" style="font-size: 0.8rem; display: block; opacity: 0.8; margin-bottom: 5px;">CAMBIO:</small>
                    <span id="vueltoResultado">$0.00</span>
                </div>
                <button class="btn-confirmar">REGISTRAR VENTA</button>
            </div>
        </div>
    </div>

    <div id="modalTarjeta" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-credit-card"></i> Pago con Tarjeta</h2>
                <span class="close-modal" onclick="cerrarModales()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="total-destacado" style="font-size: 1.6rem; padding: 15px;">
                    Monto a Cobrar: <span id="tarjetaTotal">$0.00</span>
                </div>
                <div class="form-group"><label>Nombre del Titular</label><input type="text" placeholder="Como aparece en la tarjeta"></div>
                <div class="form-group"><label>Número de Tarjeta</label><input type="text" placeholder="0000 0000 0000 0000"></div>
                <button class="btn-confirmar">PROCESAR PAGO</button>
            </div>
        </div>
    </div>

    <script src="/ElZapato/Assets/js/script.js?v=999" defer></script>
    <script>
        function cerrarModales() {
            document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
        }

        // Abrir modales
        document.querySelectorAll('.payment-method').forEach(btn => {
            btn.addEventListener('click', function() {
                const metodo = this.getAttribute('data-method');
                const totalTexto = document.getElementById('totalDisplay').innerText.replace('Total: $', '').replace(',', '');
                const totalVenta = parseFloat(totalTexto) || 0;

                if (metodo === 'efectivo') {
                    document.getElementById('efectivoTotal').innerText = '$' + totalVenta.toFixed(2);
                    document.getElementById('montoRecibido').value = '';
                    resetVueltoUI();
                    document.getElementById('modalEfectivo').style.display = 'flex';
                    document.getElementById('montoRecibido').focus();
                } else if (metodo === 'tarjeta') {
                    document.getElementById('tarjetaTotal').innerText = '$' + totalVenta.toFixed(2);
                    document.getElementById('modalTarjeta').style.display = 'flex';
                }
            });
        });

        // Reiniciar visual del cuadro de cambio
        function resetVueltoUI() {
            const res = document.getElementById('vueltoResultado');
            const eti = document.getElementById('vueltoEtiqueta');
            const cont = document.getElementById('vueltoContenedor');
            res.innerText = "$0.00";
            res.style.color = "var(--text-dark)";
            eti.innerText = "CAMBIO:";
            cont.style.borderColor = "var(--primary-soft)";
            cont.style.background = "var(--primary-light)";
        }

        // Lógica de cálculo en tiempo real con colores del sistema
        document.getElementById('montoRecibido').addEventListener('input', function() {
            // BLOQUEO DE NEGATIVOS: Si escriben menos de 0, forzamos a 0
            if (this.value < 0) { this.value = 0; }

            const totalApagar = parseFloat(document.getElementById('efectivoTotal').innerText.replace('$', '')) || 0;
            const recibido = parseFloat(this.value) || 0;
            
            const res = document.getElementById('vueltoResultado');
            const eti = document.getElementById('vueltoEtiqueta');
            const cont = document.getElementById('vueltoContenedor');

            const diferencia = recibido - totalApagar;

            if (this.value === "" || recibido === 0) {
                resetVueltoUI();
            } else if (diferencia < 0) {
                // ESTADO: DEBE (Usamos --nocolor del sistema)
                eti.innerText = "DEBE:";
                res.innerText = "-$" + Math.abs(diferencia).toFixed(2);
                res.style.color = "var(--nocolor)";
                cont.style.borderColor = "var(--nocolor)";
                cont.style.background = "#F9EBEB"; // Fondo suave rojizo
            } else {
                // ESTADO: CAMBIO (Usamos --primary-dark del sistema)
                eti.innerText = "CAMBIO:";
                res.innerText = "$" + diferencia.toFixed(2);
                res.style.color = "var(--primary-dark)"; 
                cont.style.borderColor = "var(--primary-dark)"; 
                cont.style.background = "var(--primary-light)";
            }
        });

        // Cerrar al hacer clic fuera del modal
        window.onclick = function(event) {
            if (event.target.className === 'modal-overlay') { cerrarModales(); }
        };

        // Filtrado de categorías original
        document.querySelectorAll('#categoryFilters a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelectorAll('#categoryFilters a').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                const filter = link.getAttribute('data-filter');
                document.querySelectorAll('.product-item').forEach(item => {
                    item.style.display = (filter === 'all' || item.getAttribute('data-category') === filter) ? 'flex' : 'none';
                });
            });
        });
    </script>
</body>
</html>