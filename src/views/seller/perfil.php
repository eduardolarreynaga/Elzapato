<?php
// 1. Incluir auth.php para manejar la sesión
require_once __DIR__ . '/../../config/auth.php';

// Ruta corregida al modelo
require_once __DIR__ . '/../../../model/conexion.php'; 

// 2. Seguridad: Si no hay sesión iniciada, redirigir al login
if (!is_authenticated()) {
    header("Location: /ElZapato/src/views/public/login.php");
    exit();
}

// 3. Capturar datos de la sesión
$nombreUsuario = $_SESSION['usuario'] ?? 'Usuario';
$rolUsuario    = $_SESSION['rol'] ?? 'Cajero';
$idUsuario     = $_SESSION['id_usuario'] ?? null;

// --- LÓGICA DE CAJA Y VENTAS ---
$nombreCaja = "No asignada";
$totalVentas = 0;

if ($idUsuario) {
    try {
        // Obtenemos la conexión llamando al método estático de tu clase
        $db = Conexion::conectar();

        // Obtener nombre de la caja asignada
        $stmtCaja = $db->prepare("SELECT c.nombre_caja FROM usuarios u 
                                  INNER JOIN cajas c ON u.id_caja = c.id_caja 
                                  WHERE u.id_usuario = ?");
        $stmtCaja->execute([$idUsuario]);
        $resCaja = $stmtCaja->fetch(PDO::FETCH_ASSOC);
        if ($resCaja) {
            $nombreCaja = $resCaja['nombre_caja'];
        }

        // Contar ventas realizadas
        $stmtVentas = $db->prepare("SELECT COUNT(*) as total FROM ventas WHERE id_usuario = ? AND estado = 'completada'");
        $stmtVentas->execute([$idUsuario]);
        $resVentas = $stmtVentas->fetch(PDO::FETCH_ASSOC);
        if ($resVentas) {
            $totalVentas = $resVentas['total'];
        }
    } catch (Exception $e) {
        $nombreCaja = "Error de conexión";
    }
}

// Datos de sesión restantes
$fechaActual = date('d/m/Y');
$horaActual = date('H:i:s');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - ElZapato</title>
    <link rel="stylesheet" href="/ElZapato/Assets/css/perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="perfil-container">
        <div class="perfil-card">
            <div class="perfil-header">
                <div class="perfil-avatar">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
                <h1><?php echo htmlspecialchars($nombreUsuario); ?></h1>
                <div class="role-badge">
                    <i class="fa-solid fa-tag"></i> <?php echo strtoupper(htmlspecialchars($rolUsuario)); ?>
                </div>
            </div>

            <div class="perfil-body">
                <div class="seccion-info">
                    <h3><i class="fa-solid fa-user"></i> Información Personal</h3>
                    <div class="info-grupo">
                        <div class="info-etiqueta">Usuario:</div>
                        <div class="info-valor"><?php echo htmlspecialchars($nombreUsuario); ?></div>
                    </div>
                    <div class="info-grupo">
                        <div class="info-etiqueta">Rol:</div>
                        <div class="info-valor"><?php echo htmlspecialchars($rolUsuario); ?></div>
                    </div>
                    <div class="info-grupo">
                        <div class="info-etiqueta">Caja Asignada:</div>
                        <div class="info-valor"><?php echo htmlspecialchars($nombreCaja); ?></div>
                    </div>
                    <div class="info-grupo">
                        <div class="info-etiqueta">Ventas Realizadas:</div>
                        <div class="info-valor"><?php echo $totalVentas; ?></div>
                    </div>
                </div>

                <div class="seccion-info">
                    <h3><i class="fa-solid fa-clock"></i> Información de Sesión</h3>
                    <div class="info-grupo">
                        <div class="info-etiqueta">Fecha:</div>
                        <div class="info-valor"><?php echo $fechaActual; ?></div>
                    </div>
                    <div class="info-grupo">
                        <div class="info-etiqueta">Hora:</div>
                        <div class="info-valor"><?php echo $horaActual; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cerrarModal() {
            if (window.parent && window.parent.cerrarModalPerfil) {
                window.parent.cerrarModalPerfil();
            }
        }
    </script>
</body>
</html>