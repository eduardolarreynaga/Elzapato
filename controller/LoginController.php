<?php
ob_start();
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0); 

try {
    $base = realpath(__DIR__ . '/../');
    require_once $base . '/src/config/auth.php';
    require_once $base . '/model/UsuarioModel.php';

    $u_in = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
    $p_in = isset($_POST['password']) ? trim((string)$_POST['password']) : '';

    $response = ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];

    if ($u_input !== '' && $p_input !== '') {
        $model = new UsuarioModel();
        $userData = $model->findByUsername($u_in);

        if ($userData) {
            $u_db = trim((string)$userData['nombre_usuario']);
            $p_db = trim((string)$userData['password_hash']);

            // COMPARACIÓN SIMPLE (Texto plano contra texto plano)
            if ($u_in === $u_db && $p_in === $p_db) {
                
                login_user([
                    'id_usuario' => $userData['id_usuario'],
                    'username'   => $u_db,
                    'rol'        => $userData['rol']
                ]);
                
                $response = ['success' => true];
            }
        }
    }
} catch (Throwable $e) {
    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
}

ob_clean();
echo json_encode($response);
exit;