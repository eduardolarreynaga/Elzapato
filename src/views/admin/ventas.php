<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas - Zapatería El Zapato</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ElZapato/Assets/css/styles.css">
    <link rel="icon" type="image/x-icon" href="/ElZapato/Assets/img/logo.png">
</head>
<body class="dashboard-body">
    <div class="dashboard">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="/ElZapato/Assets/img/logo.png" alt="Logo" style="height: auto; width: 60px;">
                    <h2>ElZapato</h2>
                </div>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li>
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
                    <li class="active">
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
                        <a href="empleados.php">
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
                    <i class="fas fa-user-circle"></i>
                    <div>
                        <span class="user-name">Admin</span>
                        <span class="user-role">Administrador</span>
                    </div>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <header class="dashboard-header">
                <div class="header-left">
                    <button class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Ventas</h1>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar por cliente o # venta..." id="searchVenta">
                    </div>
                    <div class="header-date">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="current-date"></span>
                    </div>
                </div>
            </header>

            <div class="stats-grid stats-list">
                <div class="stats-list-item">
                    <span class="stats-list-label"><i class="fas fa-receipt"></i> Ventas Registradas</span>
                    <span class="stats-list-value">245</span>
                </div>
                <div class="stats-list-item">
                    <span class="stats-list-label"><i class="fas fa-dollar-sign"></i> Total Facturado</span>
                    <span class="stats-list-value">$18,940</span>
                </div>
                <div class="stats-list-item">
                    <span class="stats-list-label"><i class="fas fa-credit-card"></i> Pagos con Tarjeta</span>
                    <span class="stats-list-value">98</span>
                </div>
                <div class="stats-list-item">
                    <span class="stats-list-label"><i class="fas fa-user-check"></i> Ventas con Cliente</span>
                    <span class="stats-list-value">210</span>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h3>Listado de Ventas</h3>
                    <a href="#" class="view-all"><i class="fas fa-sync"></i> Actualizar</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="ventasTable">
                        <thead>
                            <tr>
                                <th># Venta</th>
                                <th>Cliente</th>
                                <th>Cajero</th>
                                <th>Método de Pago</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>V-000245</td>
                                <td>Juan Pérez</td>
                                <td>admin</td>
                                <td>Efectivo</td>
                                <td>16/03/2026 10:15</td>
                                <td>$120.00</td>
                                <td><span class="status-badge completed">Completada</span></td>
                            </tr>
                            <tr>
                                <td>V-000244</td>
                                <td>María García</td>
                                <td>admin</td>
                                <td>Tarjeta</td>
                                <td>16/03/2026 09:48</td>
                                <td>$89.50</td>
                                <td><span class="status-badge completed">Completada</span></td>
                            </tr>
                            <tr>
                                <td>V-000243</td>
                                <td>Cliente Mostrador</td>
                                <td>admin</td>
                                <td>Transferencia</td>
                                <td>15/03/2026 18:10</td>
                                <td>$150.00</td>
                                <td><span class="status-badge pending">Pendiente</span></td>
                            </tr>
                            <tr>
                                <td>V-000242</td>
                                <td>Ana Martínez</td>
                                <td>admin</td>
                                <td>Tarjeta</td>
                                <td>15/03/2026 17:02</td>
                                <td>$73.00</td>
                                <td><span class="status-badge completed">Completada</span></td>
                            </tr>
                            <tr>
                                <td>V-000241</td>
                                <td>Carlos López</td>
                                <td>admin</td>
                                <td>Efectivo</td>
                                <td>15/03/2026 16:37</td>
                                <td>$210.00</td>
                                <td><span class="status-badge completed">Completada</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('es-ES', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        document.getElementById('searchVenta').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase().trim();
            document.querySelectorAll('#ventasTable tbody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
