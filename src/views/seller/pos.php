<?php
require_once __DIR__ . '/../../config/auth.php';

if (!is_authenticated()) {
    redirect_to('index.php');
}

$currentRole = get_current_user_role();
if (!in_array($currentRole, ['seller', 'admin'], true)) {
    redirect_to('index.php');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - Zapatería El Zapato</title>
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Fuente Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="/ElZapato/Assets/css/styles.css">
</head>
<body>
    <div class="register">
        <!-- ========== COLUMNA IZQUIERDA: TICKET DE VENTA ========== -->
        <div class="left">
            <!-- Ventana de pedido actual -->
            <div class="order-window">
                <table>
                    <thead>
                        <tr>
                            <th>Cant.</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Productos en el ticket actual -->
                        <tr>
                            <td>1</td>
                            <td>Zapato Casual - Negro T42</td>
                            <td>$45.00</td>
                            <td>$45.00</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Tenis Deportivo - Blanco T40</td>
                            <td>$60.00</td>
                            <td>$120.00</td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>Botín Cuero - Marrón T39</td>
                            <td>$75.00</td>
                            <td>$75.00</td>
                        </tr>
                        <tr>
                            <td>1</td>
                            <td>Sandalia Playa - Azul T36</td>
                            <td>$25.00</td>
                            <td>$25.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Total del pedido -->
            <div class="order-total">
                <span>Total: $265.00</span>
            </div>

            <!-- Teclado numérico y acciones -->
            <div class="buttons">
                <!-- Fila 1: Acciones principales -->
                <button class="action-btn" onclick="window.print()"><i class="fas fa-print" ></i> Imprimir</button>
                <button class="num-btn">1</button>
                <button class="num-btn">2</button>
                <button class="num-btn">3</button>
                
                <!-- Fila 2 -->
                <button class="action-btn"><i class="fas fa-ban"></i> Anular</button>
                <button class="num-btn">4</button>
                <button class="num-btn">5</button>
                <button class="num-btn">6</button>
                
                <!-- Fila 3 -->
                <button class="action-btn"><i class="fa fa-times"></i> Cant.</button>
                <button class="num-btn">7</button>
                <button class="num-btn">8</button>
                <button class="num-btn">9</button>
                
                <!-- Fila 4 -->
                <button class="action-btn exit-btn" onclick="window.location.href='/ElZapato/index.php'"><i class="fas fa-sign-out-alt"></i> Salir</button>
                <div class="empty-cell"></div>
                <button class="num-btn">0</button>
                <button class="num-btn">.00</button>
            </div>
        </div>

        <!-- ========== COLUMNA DERECHA: CATÁLOGO Y PAGOS ========== -->
        <div class="right">
            <!-- Categorías de productos -->
            <div class="categories">
                <ul>
                    <li><a href="#" class="active">Todos</a></li>
                    <li><a href="#">Deportivo</a></li>
                    <li><a href="#">Casual</a></li>
                    <li><a href="#">Formal</a></li>
                    <li><a href="#">Botas</a></li>
                    <li><a href="#">Sandalias</a></li>
                </ul>
            </div>

            <!-- Grid de productos -->
            <div class="menu-items">
                <ul>
                    <!-- Deportivos -->
                    <li class="product-item" data-category="deportivo" data-price="60.00">
                        <span class="item">Tenis Deportivo</span>
                        <span class="category">Deportivo</span>
                        <span class="price">$60.00</span>
                    </li>
                    
                    <li class="product-item" data-category="deportivo" data-price="70.00">
                        <span class="item">Tenis Running</span>
                        <span class="category">Deportivo</span>
                        <span class="price">$70.00</span>
                    </li>
                    
                    <!-- Casuales -->
                    <li class="product-item" data-category="casual" data-price="45.00">
                        <span class="item">Zapato Casual</span>
                        <span class="category">Casual</span>
                        <span class="price">$45.00</span>
                    </li>
                    
                    <li class="product-item" data-category="casual" data-price="50.00">
                        <span class="item">Mocasín</span>
                        <span class="category">Casual</span>
                        <span class="price">$50.00</span>
                    </li>
                    
                    <li class="product-item" data-category="casual" data-price="30.00">
                        <span class="item">Alpargata</span>
                        <span class="category">Casual</span>
                        <span class="price">$30.00</span>
                    </li>
                    
                    <!-- Botas -->
                    <li class="product-item" data-category="botas" data-price="75.00">
                        <span class="item">Botín Cuero</span>
                        <span class="category">Botas</span>
                        <span class="price">$75.00</span>
                    </li>
                    
                    <li class="product-item" data-category="botas" data-price="95.00">
                        <span class="item">Bota Trekking</span>
                        <span class="category">Botas</span>
                        <span class="price">$95.00</span>
                    </li>
                    
                    <li class="product-item" data-category="botas" data-price="40.00">
                        <span class="item">Bota Lluvia</span>
                        <span class="category">Botas</span>
                        <span class="price">$40.00</span>
                    </li>
                    
                    <!-- Sandalias -->
                    <li class="product-item" data-category="sandalias" data-price="25.00">
                        <span class="item">Sandalia Playa</span>
                        <span class="category">Sandalias</span>
                        <span class="price">$25.00</span>
                    </li>
                    
                    <li class="product-item" data-category="sandalias" data-price="15.00">
                        <span class="item">Ojotas</span>
                        <span class="category">Sandalias</span>
                        <span class="price">$15.00</span>
                    </li>
                    
                    <!-- Formales -->
                    <li class="product-item" data-category="formal" data-price="85.00">
                        <span class="item">Zapato Formal</span>
                        <span class="category">Formal</span>
                        <span class="price">$85.00</span>
                    </li>
                    
                    <li class="product-item" data-category="formal" data-price="90.00">
                        <span class="item">Zapato Taco</span>
                        <span class="category">Formal</span>
                        <span class="price">$90.00</span>
                    </li>
                </ul>
            </div>

            <!-- Métodos de pago -->
            <div class="payment-keys">
                <ul>
                    <li class="payment-method" data-method="efectivo">
                        <i class="fas fa-money-bill-alt fa-2x"></i>
                        <span>Efectivo</span>
                    </li>
                    <li class="payment-method" data-method="tarjeta">
                        <i class="fas fa-credit-card fa-2x"></i>
                        <span>Tarjeta</span>
                    </li>
                    <li class="payment-method" data-method="transferencia">
                        <i class="fas fa-exchange-alt fa-2x"></i>
                        <span>Transferencia</span>
                    </li>
                    <li class="payment-method" data-method="gift">
                        <i class="fas fa-gift fa-2x"></i>
                        <span>Gift Card</span>
                    </li>
                    <li class="payment-method" data-method="empleado">
                        <i class="fas fa-user fa-2x"></i>
                        <span>Empleado</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Script principal -->
    <script src="/ElZapato/Assets/js/script.js" defer></script>
</body>
</html>