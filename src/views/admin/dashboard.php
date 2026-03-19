<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Zapatería El Zapato</title>
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Fuente Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="/ElZapato/Assets/css/styles.css">
        <link rel="icon" type="image/x-icon" href="/ElZapato/Assets/img/logo.png">
</head>
<body class="dashboard-body">
    <div class="dashboard">
        <!-- ========== SIDEBAR ========== -->
        <aside class="sidebar">
            <div class="sidebar-header">
                
                <div class="logo">
                    <!-- <i class="fas fa-shoe-prints fa-2x"></i> -->
                    <img src="/ElZapato/Assets/img/logo.png" style="height: auto; width: 60px;" alt="">    
                    <h2>ElZapato</h2>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="productos.php">
                            <i class="fas fa-box"></i>
                            <span>Productos</span>
                        </a>
                    </li>
                    <li hidden>
                        <a href="#">
                            <i class="fas fa-tags"></i>
                            <span>Categorías</span>
                        </a>
                    </li>
                    <li>
                        <a href="ventas.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Ventas</span>
                        </a>
                    </li>
                    <li>
                        <a href="clientes.php">
                            <i class="fas fa-users"></i>
                            <span>Clientes</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-user-tie"></i>
                            <span>Empleados</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-truck"></i>
                            <span>Proveedores</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fas fa-chart-line"></i>
                            <span>Reportes</span>
                        </a>
                    </li>
                    <li class="separator logout-separator"></li>
                    <li hidden>
                        <a href="#">
                            <i class="fas fa-cog"></i>
                            <span>Configuración</span>
                        </a>
                    </li>
                    <li class="logout-item">
                        <a href="/ElZapato/index.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Salir</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <i class="fas fa-user-circle fa-2x"></i>
                    <div>
                        <span class="user-name">Usuario: Admin</span>
                        <span class="user-role">Administrador</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ========== MAIN CONTENT ========== -->
        <main class="main-content">
            <!-- Header -->
            <header class="dashboard-header">
                <div class="header-left">
                    <button class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Dashboard</h1>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar...">
                    </div>
                    <div class="header-notifications" hidden>
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    <div class="header-date">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="current-date"></span>
                    </div>
                </div>
            </header>

        

            <!-- Tables Section -->
            <div class="tables-grid">
                <!-- Productos más vendidos -->
                <div class="table-card">
                    <div class="table-header">
                        <h3>Productos Más Vendidos</h3>
                        <a href="#" class="view-all">Ver todos <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Cantidad</th>
                                    <th>Ingresos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <span class="product-name">Tenis Deportivo</span>
                                            <span class="product-sku">#TND-001</span>
                                        </div>
                                    </td>
                                    <td>Deportivo</td>
                                    <td>45</td>
                                    <td>$2,700.00</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <span class="product-name">Botín Cuero</span>
                                            <span class="product-sku">#BTC-023</span>
                                        </div>
                                    </td>
                                    <td>Botas</td>
                                    <td>32</td>
                                    <td>$2,400.00</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <span class="product-name">Zapato Casual</span>
                                            <span class="product-sku">#ZPC-045</span>
                                        </div>
                                    </td>
                                    <td>Casual</td>
                                    <td>28</td>
                                    <td>$1,260.00</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <span class="product-name">Sandalia Playa</span>
                                            <span class="product-sku">#SND-012</span>
                                        </div>
                                    </td>
                                    <td>Sandalias</td>
                                    <td>25</td>
                                    <td>$625.00</td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="product-cell">
                                            <span class="product-name">Zapato Formal</span>
                                            <span class="product-sku">#ZPF-078</span>
                                        </div>
                                    </td>
                                    <td>Formal</td>
                                    <td>18</td>
                                    <td>$1,530.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Últimas ventas -->
                <div class="table-card">
                    <div class="table-header">
                        <h3>Últimas Ventas</h3>
                        <a href="#" class="view-all">Ver todos <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th># Venta</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#V00125</td>
                                    <td>Juan Pérez</td>
                                    <td>12/03/2026</td>
                                    <td>$120.00</td>
                                    <td><span class="status-badge completed">Completada</span></td>
                                </tr>
                                <tr>
                                    <td>#V00124</td>
                                    <td>María García</td>
                                    <td>12/03/2026</td>
                                    <td>$85.00</td>
                                    <td><span class="status-badge completed">Completada</span></td>
                                </tr>
                                <tr>
                                    <td>#V00123</td>
                                    <td>Carlos López</td>
                                    <td>11/03/2026</td>
                                    <td>$210.00</td>
                                    <td><span class="status-badge completed">Completada</span></td>
                                </tr>
                                <tr>
                                    <td>#V00122</td>
                                    <td>Ana Martínez</td>
                                    <td>11/03/2026</td>
                                    <td>$75.00</td>
                                    <td><span class="status-badge pending">Pendiente</span></td>
                                </tr>
                                <tr>
                                    <td>#V00121</td>
                                    <td>Roberto Sánchez</td>
                                    <td>10/03/2026</td>
                                    <td>$155.00</td>
                                    <td><span class="status-badge completed">Completada</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Stock bajo alerta -->
            <div class="alert-card">
                <div class="alert-header">
                    <h3><i class="fas fa-exclamation-triangle"></i> Productos con Stock Bajo</h3>
                </div>
                <div class="alert-content">
                    <div class="stock-item">
                        <div class="stock-info">
                            <span class="stock-name">Tenis Deportivo - Blanco T42</span>
                            <span class="stock-code">#TND-001-42</span>
                        </div>
                        <div class="stock-status">
                            <span class="stock-current">5 uds</span>
                            <span class="stock-min">Mín: 10 uds</span>
                            <button class="btn-restock"><i class="fas fa-plus"></i> Reponer</button>
                        </div>
                    </div>
                    <div class="stock-item">
                        <div class="stock-info">
                            <span class="stock-name">Botín Cuero - Marrón T39</span>
                            <span class="stock-code">#BTC-023-39</span>
                        </div>
                        <div class="stock-status">
                            <span class="stock-current">3 uds</span>
                            <span class="stock-min">Mín: 8 uds</span>
                            <button class="btn-restock"><i class="fas fa-plus"></i> Reponer</button>
                        </div>
                    </div>
                    <div class="stock-item">
                        <div class="stock-info">
                            <span class="stock-name">Zapato Formal - Negro T41</span>
                            <span class="stock-code">#ZPF-078-41</span>
                        </div>
                        <div class="stock-status">
                            <span class="stock-current">4 uds</span>
                            <span class="stock-min">Mín: 10 uds</span>
                            <button class="btn-restock"><i class="fas fa-plus"></i> Reponer</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Fecha actual
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        // Gráfico de ventas
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Ventas ($)',
                    data: [850, 1200, 950, 1450, 1680, 2100, 1850],
                    borderColor: '#AB886D',
                    backgroundColor: 'rgba(171, 136, 109, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Gráfico de categorías
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Deportivo', 'Casual', 'Botas', 'Formal', 'Sandalias'],
                datasets: [{
                    data: [35, 25, 20, 12, 8],
                    backgroundColor: [
                        '#AB886D',
                        '#D6C0B3',
                        '#E4E0E1',
                        '#C1A392',
                        '#B89A87'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });

        // Toggle menu para móviles
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>