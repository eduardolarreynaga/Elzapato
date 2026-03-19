<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Zapatería El Zapato</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Fuente Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
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
                    <li class="active">
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

         <!-- ========== MAIN CONTENT ========== -->
        <main class="main-content">
            <!-- Header -->
            <header class="dashboard-header">
                <div class="header-left">
                    <button class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1>Productos</h1>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar productos..." id="searchProduct">
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
            
            
            <div class="stats-grid stats-list">
                <div class="stats-list-item">
                    <span class="stats-list-label"><i class="fas fa-boxes"></i> Total</span>
                    <span class="stats-list-value">156</span>
                </div>
                <div class="stats-list-item">
                    <span class="stats-list-label"><i class="fas fa-check-circle"></i> Activos</span>
                    <span class="stats-list-value">142</span>
                </div>
                <div class="stats-list-item">
                    <span class="stats-list-label"><i class="fas fa-exclamation-triangle"></i> Stock Bajo</span>
                    <span class="stats-list-value">8</span>
                </div>
                <div class="stats-list-item">
                    <span class="stats-list-label"><i class="fas fa-dollar-sign"></i> Valor</span>
                    <span class="stats-list-value">$45.8k</span>
                </div>
            </div>


            <!-- Barra de acciones -->
            <div class="actions-bar">
                <div class="actions-left">
                    <button class="btn-primary" id="btnNuevoProducto">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </button>
                    <div class="filters">
                        <select class="filter-select" id="filterCategory">
                            <option value="">Categoría</option>
                            <option value="deportivo">Deportivo</option>
                            <option value="casual">Casual</option>
                            <option value="formal">Formal</option>
                            <option value="botas">Botas</option>
                            <option value="sandalias">Sandalias</option>
                        </select>
                        <select class="filter-select" id="filterStatus">
                            <option value="">Estado</option>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                            <option value="bajo_stock">Stock Bajo</option>
                        </select>
                    </div>
                </div>
                <div class="actions-right">
                    <button class="btn-icon" title="Importar">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn-icon" title="Exportar">
                        <i class="fas fa-upload"></i>
                    </button>
                </div>
            </div>

            <!-- Tabla de productos -->
            <div class="table-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        <!-- Deportivos -->
                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Tenis Deportivo</span>
                                        <span class="product-sku">TND-001</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Deportivo</span></td>
                            <td>$60.00</td>
                            <td>
                                <span class="stock-badge stock-ok">45</span>
                            </td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small" title="Editar" onclick="editProduct('P001')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-icon small" title="Más" onclick="showMenu('P001')">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Tenis Running</span>
                                        <span class="product-sku">TNR-002</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Deportivo</span></td>
                            <td>$70.00</td>
                            <td>
                                <span class="stock-badge stock-ok">32</span>
                            </td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon small"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Tenis Basketball</span>
                                        <span class="product-sku">TNB-003</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Deportivo</span></td>
                            <td>$120.00</td>
                            <td>
                                <span class="stock-badge stock-warning">8</span>
                            </td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon small"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>

                        <!-- Casuales -->
                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Zapato Casual</span>
                                        <span class="product-sku">ZPC-004</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Casual</span></td>
                            <td>$45.00</td>
                            <td>
                                <span class="stock-badge stock-ok">56</span>
                            </td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon small"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Mocasín</span>
                                        <span class="product-sku">MCS-005</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Casual</span></td>
                            <td>$50.00</td>
                            <td>
                                <span class="stock-badge stock-warning">7</span>
                            </td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon small"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Alpargata</span>
                                        <span class="product-sku">ALP-006</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Casual</span></td>
                            <td>$30.00</td>
                            <td>
                                <span class="stock-badge stock-ok">23</span>
                            </td>
                            <td><span class="badge badge-inactive">Inactivo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon small"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>

                        <!-- Botas -->
                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Botín Cuero</span>
                                        <span class="product-sku">BTC-007</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Botas</span></td>
                            <td>$75.00</td>
                            <td>
                                <span class="stock-badge stock-warning">3</span>
                            </td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon small"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Bota Trekking</span>
                                        <span class="product-sku">BTR-008</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Botas</span></td>
                            <td>$95.00</td>
                            <td>
                                <span class="stock-badge stock-ok">15</span>
                            </td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon small"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Bota Lluvia</span>
                                        <span class="product-sku">BLL-009</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Botas</span></td>
                            <td>$40.00</td>
                            <td>
                                <span class="stock-badge stock-ok">28</span>
                            </td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon small"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>

                        <!-- Formales -->
                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Zapato Formal</span>
                                        <span class="product-sku">ZPF-010</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Formal</span></td>
                            <td>$85.00</td>
                            <td>
                                <span class="stock-badge stock-ok">22</span>
                            </td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon small"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Zapato Taco</span>
                                        <span class="product-sku">ZPT-011</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Formal</span></td>
                            <td>$90.00</td>
                            <td>
                                <span class="stock-badge stock-warning">6</span>
                            </td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon small"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>

                        <!-- Sandalias -->
                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Sandalia Playa</span>
                                        <span class="product-sku">SND-012</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Sandalias</span></td>
                            <td>$25.00</td>
                            <td>
                                <span class="stock-badge stock-ok">67</span>
                            </td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon small"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td><input type="checkbox" class="product-select"></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-details">
                                        <span class="product-name">Ojotas</span>
                                        <span class="product-sku">OJT-013</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-category">Sandalias</span></td>
                            <td>$15.00</td>
                            <td>
                                <span class="stock-badge stock-ok">89</span>
                            </td>
                            <td><span class="badge badge-active">Activo</span></td>
                            <td>
                                <div class="actions-cell">
                                    <button class="btn-icon small"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon small"><i class="fas fa-ellipsis-v"></i></button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación simple -->
            <div class="pagination-simple">
                <span class="pagination-info">Mostrando 1-13 de 156 productos</span>
                <div class="pagination-pages">
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">4</button>
                    <button class="page-btn">5</button>
                    <span class="page-dots">...</span>
                    <button class="page-btn">13</button>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Simple -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-box"></i> Nuevo Producto</h3>
                <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <div class="form-group">
                        <label>Nombre del Producto</label>
                        <input type="text" class="form-control" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Categoría</label>
                            <select class="form-control">
                                <option>Deportivo</option>
                                <option>Casual</option>
                                <option>Formal</option>
                                <option>Botas</option>
                                <option>Sandalias</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Marca</label>
                            <select class="form-control">
                                <option>Nike</option>
                                <option>Adidas</option>
                                <option>Flexi</option>
                                <option>Cat</option>
                                <option>Havaianas</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Precio Venta</label>
                            <input type="number" class="form-control" step="0.01">
                        </div>
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeModal()">Cancelar</button>
                <button class="btn-primary" onclick="saveProduct()">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Menú contextual para acciones -->
    <div class="context-menu" id="contextMenu">
        <ul>
            <li onclick="showVariants()"><i class="fas fa-pallet"></i> Variantes</li>
            <li onclick="showHistory()"><i class="fas fa-history"></i> Historial</li>
            <li class="separator"></li>
            <li onclick="deleteProduct()" class="text-danger"><i class="fas fa-trash"></i> Eliminar</li>
        </ul>
    </div>

    <script>
        // Fecha actual
        document.getElementById('current-date').textContent = new Date().toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        // Toggle menú móvil
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Select All
        document.getElementById('selectAll').addEventListener('change', function(e) {
            document.querySelectorAll('.product-select').forEach(cb => cb.checked = e.target.checked);
        });

        // Búsqueda
        document.getElementById('searchProduct').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('#productsTableBody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });

        // Filtros
        function setupFilter(selectId, filterFn) {
            document.getElementById(selectId).addEventListener('change', function(e) {
                const value = e.target.value;
                document.querySelectorAll('#productsTableBody tr').forEach(row => {
                    row.style.display = filterFn(row, value) ? '' : 'none';
                });
            });
        }

        setupFilter('filterCategory', (row, value) => {
            if (!value) return true;
            const category = row.querySelector('.badge-category')?.textContent.toLowerCase() || '';
            return category.includes(value);
        });

        setupFilter('filterStatus', (row, value) => {
            if (!value) return true;
            if (value === 'bajo_stock') {
                return row.querySelector('.stock-badge')?.classList.contains('stock-warning') || false;
            }
            const status = row.querySelector('.badge-active, .badge-inactive')?.textContent.toLowerCase() || '';
            return status.includes(value);
        });

        // Modal
        function openModal() {
            document.getElementById('productModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('productModal').classList.remove('active');
        }

        function saveProduct() {
            alert('Producto guardado correctamente');
            closeModal();
        }

        // Menú contextual
        let currentProduct = null;
        
        function showMenu(productId) {
            currentProduct = productId;
            const menu = document.getElementById('contextMenu');
            menu.classList.add('active');
            
            // Cerrar al hacer clic fuera
            setTimeout(() => {
                document.addEventListener('click', function closeMenu(e) {
                    if (!menu.contains(e.target) && !e.target.closest('.btn-icon')) {
                        menu.classList.remove('active');
                        document.removeEventListener('click', closeMenu);
                    }
                });
            }, 100);
        }

        function showVariants() {
            alert('Mostrar variantes del producto');
            document.getElementById('contextMenu').classList.remove('active');
        }

        function showHistory() {
            alert('Mostrar historial del producto');
            document.getElementById('contextMenu').classList.remove('active');
        }

        function deleteProduct() {
            if (confirm('¿Eliminar producto?')) {
                alert('Producto eliminado');
            }
            document.getElementById('contextMenu').classList.remove('active');
        }

        // Botón nuevo producto
        document.getElementById('btnNuevoProducto').addEventListener('click', openModal);
    </script>
</body>
</html>