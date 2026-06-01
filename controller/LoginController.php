<?php
ob_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0); 

try {
    $base = realpath(__DIR__ . '/../');
    require_once $base . '/src/config/auth.php';
    require_once $base . '/model/UsuarioModel.php';
    require_once $base . '/model/conexion.php';
    require_once $base . '/helpers/LogHelper.php';

    $u_in = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
    $p_in = isset($_POST['password']) ? trim((string)$_POST['password']) : '';

    $response = ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];

    if ($u_in !== '' && $p_in !== '') {
        $model = new UsuarioModel();
        $userData = $model->findByUsername($u_in);

        if ($userData) {
            $u_db = trim((string)$userData['nombre_usuario']);
            $p_db = trim((string)$userData['password_hash']);

            $isPasswordValid = password_verify($p_in, $p_db);
            $esLegacyPlano = !$isPasswordValid && ($p_in === $p_db);

            if ($u_in === $u_db && ($isPasswordValid || $esLegacyPlano)) {

                if ($esLegacyPlano) {
                    try {
                        $nuevoHash = password_hash($p_in, PASSWORD_DEFAULT);
                        $db = Conexion::conectar();
                        $stmt = $db->prepare('UPDATE usuarios SET password_hash = :hash WHERE id_usuario = :id');
                        $stmt->execute([
                            ':hash' => $nuevoHash,
                            ':id' => (int)$userData['id_usuario']
                        ]);
                    } catch (Throwable $e) {
                    }
                }
                
                login_user([
                    'id_usuario' => $userData['id_usuario'],
                    'username'   => $u_db,
                    'rol'        => $userData['rol']
                ]);
                
                // Registrar login exitoso
                LogHelper::registrar('login', 'usuarios', $userData['id_usuario'], 'Inicio de sesión exitoso');
                
                $response = ['success' => true];
            } else {
                // Registrar login fallido
                LogHelper::registrar('login_fallido', 'usuarios', null, "Intento fallido de login para usuario: $u_in");
            }
        } else {
            // Registrar login fallido (usuario no existe)
            LogHelper::registrar('login_fallido', 'usuarios', null, "Intento fallido de login para usuario inexistente: $u_in");
        }
    }
} catch (Throwable $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

ob_clean();
echo json_encode($response);
exit;