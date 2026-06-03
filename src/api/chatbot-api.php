<?php
/**
 * Chatbot El Zapato - v3.0
 * Arquitectura: BD → contexto completo → IA como motor principal → fallback local
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Leer .env manualmente (sin composer ni librerías)
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

require_once __DIR__ . '/../../model/conexion.php';

define('OPENROUTER_API_KEY', $_ENV['OPENROUTER_API_KEY']);
define('OPENROUTER_URL',     'https://openrouter.ai/api/v1/chat/completions');
define('AI_MODEL',           'openai/gpt-4o-mini');
define('AI_TIMEOUT',         12);

// ── Utilidades ─────────────────────────────────────────────────────────────────

function sanitize(string $input): string {
    return trim(htmlspecialchars(strip_tags($input)));
}

function markdownAHTML(string $texto): string {
    // **texto** → <strong>texto</strong>
    $texto = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $texto);
    // *texto* → <em>texto</em>
    $texto = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $texto);
    // Listas numeradas: "1. texto" → "• texto<br>"
    $texto = preg_replace('/^\s*\d+\.\s+(.+)$/m', '• $1<br>', $texto);
    // Listas con guión o bullet: "- texto" / "• texto" → "• texto<br>"
    $texto = preg_replace('/^\s*[-•]\s+(.+)$/m', '• $1<br>', $texto);
    // Saltos de línea dobles → <br><br>
    $texto = preg_replace('/\n{2,}/', '<br><br>', $texto);
    // Saltos simples restantes → <br>
    $texto = preg_replace('/\n/', '<br>', $texto);

    return trim($texto);
}

// ── Respuestas instantáneas (sin BD ni IA) ─────────────────────────────────────

function respuestaRapida(string $q): ?string {
    $t = strtolower($q);
    $mapa = [
        '/^(hola|buenas|hey|saludos|buenos dia|buenas tarde|buenas noche)/i'
            => "👋 ¡Hola! Soy el asistente virtual de <strong>El Zapato</strong>. Puedo ayudarte con productos, precios, tallas, marcas y más. ¿Qué necesitas?",
        '/(gracias|adios|chao|bye|hasta luego)/i'
            => "👋 ¡Gracias a ti! Te esperamos en El Zapato. 😊",
        '/(horario|cuando abren|hora|atienden)/i'
            => "🕒 <strong>Horario:</strong> Lunes–Sábado 8AM–5PM · Domingos 8AM–12PM",
        '/(donde estan|ubicacion|direccion|como llegar|donde queda)/i'
            => "📍 Km 51, Cantón Agua Zarca, Ilobasco, El Salvador.",
        '/(telefono|contacto|whatsapp|numero|llamar)/i'
            => "📞 <strong>(503) 2378-1500</strong> — También por WhatsApp.",
        '/(envio|delivery|domicilio|envian)/i'
            => "🚚 Por el momento <strong>no hacemos envíos</strong>. Las compras son en tienda física.",
        '/(devolucion|cambio|garantia|reembolso)/i'
            => "🔄 <strong>15 días</strong> para devoluciones. El producto debe estar sin usar y con empaque original.",
        '/(como pagan|forma de pago|tarjeta|efectivo|transferencia)/i'
            => "💳 Aceptamos <strong>Efectivo, Tarjeta</strong> (Visa/MasterCard) y <strong>Transferencia</strong>. ¡Damos factura!",
        '/(ayuda|que puedes|que sabes|que haces)/i'
            => "🤖 Puedo ayudarte con:<br>• Buscar zapatos por marca, color, talla o categoría<br>• Ver precios y disponibilidad<br>• Los más vendidos o mejores ofertas<br>• Información de la tienda<br><br>¡Solo pregúntame con tus palabras! 😊",
    ];

    foreach ($mapa as $patron => $resp) {
        if (preg_match($patron, $t)) return $resp;
    }
    return null;
}

// ── Construcción del contexto desde la BD ──────────────────────────────────────

function extraerFiltros(string $pregunta): array {
    $t = strtolower($pregunta);

    // Marcas
    $marcas_conocidas = ['nike','adidas','puma','converse','vans','skechers','new balance','reebok','timberland'];
    $marca_detectada = null;
    foreach ($marcas_conocidas as $m) {
        if (str_contains($t, $m)) { $marca_detectada = $m; break; }
    }

    // Categorías
    $cat_detectada = null;
    $cats = [
        'deportivo' => 'Deportivos', 'deporte' => 'Deportivos', 'running' => 'Deportivos',
        'correr' => 'Deportivos', 'gym' => 'Deportivos', 'ejercicio' => 'Deportivos',
        'casual' => 'Casuales', 'cotidiano' => 'Casuales', 'diario' => 'Casuales',
        'formal' => 'Formales', 'boda' => 'Formales', 'elegante' => 'Formales',
        'oficina' => 'Formales', 'graduacion' => 'Formales', 'traje' => 'Formales',
        'sandalia' => 'Sandalias', 'chancla' => 'Sandalias', 'playa' => 'Sandalias',
        'bota' => 'Botas', 'botines' => 'Botas',
        'urbano' => 'Urbanos', 'skate' => 'Urbanos', 'street' => 'Urbanos',
    ];
    foreach ($cats as $kw => $cat) {
        if (str_contains($t, $kw)) { $cat_detectada = $cat; break; }
    }

    // Colores
    $colores_map = [
        'negr' => 'Negro', 'blanc' => 'Blanco', 'roj' => 'Rojo', 'azul' => 'Azul',
        'verde' => 'Verde', 'gris' => 'Gris', 'cafe' => 'Café', 'café' => 'Café',
        'beige' => 'Beige', 'marron' => 'Café', 'marrón' => 'Café',
    ];
    $color_detectado = null;
    foreach ($colores_map as $kw => $color) {
        if (str_contains($t, $kw)) { $color_detectado = $color; break; }
    }

    // Talla
    $talla_detectada = null;
    if (preg_match('/(?:talla|numero|n[uú]mero|talle)[^\d]*(\d{2})/i', $t, $m)) {
        $talla_detectada = $m[1];
    } elseif (preg_match('/\b(3[6-9]|4[0-6])\b/', $t, $m)) {
        $talla_detectada = $m[1];
    }

    // Intención de precio
    $intencion_precio = null;
    if (preg_match('/barato|económi|menor precio|precio bajo|accesible|ganga|oferta/i', $t))  $intencion_precio = 'barato';
    if (preg_match('/caro|costoso|premium|lujo|exclusivo|mayor precio/i', $t))                $intencion_precio = 'caro';

    // Rango de precio
    $precio_max = null;
    $precio_min = null;
    if (preg_match('/menos de \$?(\d+)/i', $t, $m))    $precio_max = (float)$m[1];
    if (preg_match('/m[aá]s de \$?(\d+)/i', $t, $m))   $precio_min = (float)$m[1];
    if (preg_match('/hasta \$?(\d+)/i', $t, $m))        $precio_max = (float)$m[1];
    if (preg_match('/desde \$?(\d+)/i', $t, $m))        $precio_min = (float)$m[1];

    // Popularidad
    $popular = preg_match('/mas vendido|popular|top|favorito|recomendado|moda/i', $t);

    // Nombre de producto
    $nombre_producto = null;
    if (preg_match('/(air max pulse|air zoom pegasus|air max|grand court|ultraboost|velocity nitro|chuck taylor|all star|old skool|sk8.hi|arch fit|d\'lites|dlites|club c|nano x3|574|327|oxford|botin urban|sandalia comfort|sandalia teva|teva hurricane|runner street|new slides|puma cali|cali sport|timberland)/i', $t, $m)) {
        $nombre_producto = $m[1];
    }

    return compact(
        'marca_detectada','cat_detectada','color_detectado','talla_detectada',
        'intencion_precio','precio_max','precio_min','popular','nombre_producto'
    );
}

function obtenerContextoBD(string $pregunta): array {
    $db  = Conexion::conectar();
    $f   = extraerFiltros($pregunta);
    $ctx = ['filtros' => $f, 'productos' => [], 'marcas' => [], 'categorias' => []];

    $ctx['marcas']     = $db->query("SELECT nombre_marca FROM marcas ORDER BY nombre_marca")->fetchAll(PDO::FETCH_COLUMN);
    $ctx['categorias'] = $db->query("SELECT nombre_categoria FROM categorias ORDER BY nombre_categoria")->fetchAll(PDO::FETCH_COLUMN);

    $where  = ["p.estado = 'activo'", "pv.estado = 'activo'"];
    $params = [];
    $order  = "p.nombre_producto ASC";

    if ($f['marca_detectada']) {
        $where[]          = "LOWER(m.nombre_marca) LIKE :marca";
        $params[':marca']  = '%' . $f['marca_detectada'] . '%';
    }
    if ($f['cat_detectada']) {
        $where[]          = "c.nombre_categoria = :cat";
        $params[':cat']    = $f['cat_detectada'];
    }
    if ($f['color_detectado']) {
        $where[]          = "LOWER(pv.color) LIKE :color";
        $params[':color']  = '%' . strtolower($f['color_detectado']) . '%';
    }
    if ($f['talla_detectada']) {
        $where[]          = "pv.talla = :talla";
        $params[':talla']  = $f['talla_detectada'];
    }
    if ($f['precio_max'] !== null) {
        $where[]          = "pv.precio_venta <= :pmax";
        $params[':pmax']   = $f['precio_max'];
    }
    if ($f['precio_min'] !== null) {
        $where[]          = "pv.precio_venta >= :pmin";
        $params[':pmin']   = $f['precio_min'];
    }
    if ($f['nombre_producto']) {
        $where[]           = "LOWER(p.nombre_producto) LIKE :nombre";
        $params[':nombre']  = '%' . strtolower($f['nombre_producto']) . '%';
    }

    if ($f['intencion_precio'] === 'barato') $order = "MIN(pv.precio_venta) ASC";
    if ($f['intencion_precio'] === 'caro')   $order = "MIN(pv.precio_venta) DESC";
    if ($f['popular'])                        $order = "vendidos DESC";

    $whereStr = implode(' AND ', $where);

    $sql = "
        SELECT
            p.nombre_producto,
            m.nombre_marca,
            c.nombre_categoria,
            MIN(pv.precio_venta) AS precio_desde,
            MAX(pv.precio_venta) AS precio_hasta,
            (SELECT COALESCE(SUM(pv2.stock),0) FROM producto_variante pv2 WHERE pv2.id_producto = p.id_producto AND pv2.estado = 'activo') AS stock_total,
            GROUP_CONCAT(DISTINCT pv.talla ORDER BY CAST(pv.talla AS UNSIGNED) SEPARATOR ', ') AS tallas,
            GROUP_CONCAT(DISTINCT pv.color ORDER BY pv.color SEPARATOR ', ') AS colores,
            (SELECT COALESCE(SUM(dv2.cantidad),0) FROM detalle_venta dv2 INNER JOIN producto_variante pv3 ON dv2.id_variante = pv3.id_variante WHERE pv3.id_producto = p.id_producto) AS vendidos
        FROM productos p
        JOIN marcas m             ON p.id_marca     = m.id_marca
        JOIN categorias c         ON p.id_categoria = c.id_categoria
        JOIN producto_variante pv ON p.id_producto  = pv.id_producto
        WHERE {$whereStr}
        GROUP BY p.id_producto
        ORDER BY {$order}
        LIMIT 8
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $ctx['productos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sin filtros → top ventas como sugerencia
    if (empty($ctx['productos']) && empty($params)) {
        $ctx['productos'] = $db->query("
            SELECT p.nombre_producto, m.nombre_marca, c.nombre_categoria,
                   MIN(pv.precio_venta) AS precio_desde, MAX(pv.precio_venta) AS precio_hasta,
                   (SELECT COALESCE(SUM(pv2.stock),0) FROM producto_variante pv2 WHERE pv2.id_producto = p.id_producto AND pv2.estado = 'activo') AS stock_total,
                   GROUP_CONCAT(DISTINCT pv.talla ORDER BY CAST(pv.talla AS UNSIGNED) SEPARATOR ', ') AS tallas,
                   GROUP_CONCAT(DISTINCT pv.color ORDER BY pv.color SEPARATOR ', ') AS colores,
                   (SELECT COALESCE(SUM(dv2.cantidad),0) FROM detalle_venta dv2 INNER JOIN producto_variante pv3 ON dv2.id_variante = pv3.id_variante WHERE pv3.id_producto = p.id_producto) AS vendidos
            FROM productos p
            JOIN marcas m         ON p.id_marca = m.id_marca
            JOIN categorias c     ON p.id_categoria = c.id_categoria
            JOIN producto_variante pv ON p.id_producto = pv.id_producto AND pv.estado = 'activo'
            WHERE p.estado = 'activo'
            GROUP BY p.id_producto
            ORDER BY vendidos DESC
            LIMIT 6
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    return $ctx;
}

// ── System prompt para la IA ───────────────────────────────────────────────────

function construirSystemPrompt(array $ctx): string {
    $filtros = $ctx['filtros'];

    $sys  = "Eres el asistente virtual de **El Zapato**, zapatería en El Salvador.\n";
    $sys .= "📍 Km 51, Cantón Agua Zarca, Ilobasco | 📞 (503) 2378-1500\n";
    $sys .= "🕒 Lun–Sáb 8AM–5PM | Dom 8AM–12PM\n";
    $sys .= "💳 Pagos: Efectivo, Tarjeta, Transferencia\n\n";

    $sys .= "🏷️ **Marcas disponibles:** " . implode(', ', $ctx['marcas']) . "\n";
    $sys .= "📂 **Categorías:** " . implode(', ', $ctx['categorias']) . "\n\n";

    $filtros_activos = array_filter([
        'Marca'     => $filtros['marca_detectada'],
        'Categoría' => $filtros['cat_detectada'],
        'Color'     => $filtros['color_detectado'],
        'Talla'     => $filtros['talla_detectada'],
        'Precio ≤'  => $filtros['precio_max'] ? '$' . $filtros['precio_max'] : null,
        'Precio ≥'  => $filtros['precio_min'] ? '$' . $filtros['precio_min'] : null,
    ]);
    if (!empty($filtros_activos)) {
        $sys .= "🔍 **Filtros aplicados:** ";
        foreach ($filtros_activos as $k => $v) $sys .= "{$k}: {$v}  ";
        $sys .= "\n\n";
    }

    if (!empty($ctx['productos'])) {
        $sys .= "📦 **INVENTARIO REAL (usa SOLO estos datos, no inventes precios ni productos):**\n";
        foreach ($ctx['productos'] as $p) {
            $precio = ($p['precio_desde'] == $p['precio_hasta'])
                ? '$' . number_format($p['precio_desde'], 2)
                : '$' . number_format($p['precio_desde'], 2) . ' – $' . number_format($p['precio_hasta'], 2);

            $sys .= "• **{$p['nombre_producto']}** | {$p['nombre_marca']} | {$p['nombre_categoria']}\n";
            $sys .= "  Precio: {$precio} | Stock: {$p['stock_total']} | Tallas: {$p['tallas']} | Colores: {$p['colores']} | Vendidos: {$p['vendidos']}\n";
        }
    } else {
        $sys .= "⚠️ No se encontraron productos con esos filtros. Indícale al usuario que puede probar con otros criterios.\n";
    }

    $sys .= "\n**REGLAS:**\n";
    $sys .= "- Responde siempre en español, amigable y conciso.\n";
    $sys .= "- Usa emojis con moderación para hacer la respuesta visual.\n";
    $sys .= "- Usa los precios y datos exactos del inventario. NUNCA inventes datos.\n";
    $sys .= "- Si hay pocos resultados, mencionalo y sugiere ampliar la búsqueda.\n";
    $sys .= "- Si el usuario pregunta algo fuera del catálogo (clima, recetas, etc.), responde solo sobre la tienda.\n";
    $sys .= "- Formato: usa saltos de línea simples (\\n) y negritas con **texto**. NO uses HTML en tu respuesta.\n";

    return $sys;
}

// ── Llamada a la IA ────────────────────────────────────────────────────────────

function llamarIA(string $pregunta, string $systemPrompt): array {
    $body = json_encode([
        'model'    => AI_MODEL,
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $pregunta],
        ],
        'max_tokens'  => 350,
        'temperature' => 0.3,
    ]);

    $ch = curl_init(OPENROUTER_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENROUTER_API_KEY,
            'HTTP-Referer: http://localhost',
            'X-Title: ElZapato-v3',
        ],
        CURLOPT_TIMEOUT        => AI_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $resp     = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        error_log("[El Zapato Chatbot] cURL error: {$curlErr}");
        return ['ok' => false, 'error' => 'curl_error', 'detail' => $curlErr];
    }
    if ($httpCode !== 200) {
        error_log("[El Zapato Chatbot] HTTP {$httpCode}: {$resp}");
        return ['ok' => false, 'error' => "http_{$httpCode}", 'detail' => $resp];
    }

    $data  = json_decode($resp, true);
    $texto = trim($data['choices'][0]['message']['content'] ?? '');

    if (empty($texto)) {
        return ['ok' => false, 'error' => 'empty_response'];
    }

    return ['ok' => true, 'texto' => $texto];
}

// ── Fallback local (sin IA) ────────────────────────────────────────────────────

function fallbackLocal(array $ctx): string {
    $productos = $ctx['productos'];

    if (empty($productos)) {
        return "😕 No encontré productos con esos criterios.<br><br>"
            . "Puedes buscar por:<br>"
            . "• <strong>Marca:</strong> " . implode(', ', $ctx['marcas']) . "<br>"
            . "• <strong>Categoría:</strong> " . implode(', ', $ctx['categorias']) . "<br>"
            . "• <strong>Color, talla o precio</strong><br><br>"
            . "¿Cómo te puedo ayudar? 🤔";
    }

    $resp = "📦 <strong>Encontré estos productos:</strong><br><br>";
    foreach ($productos as $p) {
        $precio = ($p['precio_desde'] == $p['precio_hasta'])
            ? '$' . number_format($p['precio_desde'], 2)
            : '$' . number_format($p['precio_desde'], 2) . ' – $' . number_format($p['precio_hasta'], 2);

        $resp .= "👟 <strong>{$p['nombre_producto']}</strong> ({$p['nombre_marca']})<br>";
        $resp .= "&nbsp;&nbsp;💰 {$precio} | 📦 Stock: {$p['stock_total']}<br>";
        $resp .= "&nbsp;&nbsp;📏 Tallas: {$p['tallas']} | 🎨 {$p['colores']}<br><br>";
    }
    return $resp;
}

// ── ENDPOINT PRINCIPAL ─────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'respuesta' => 'Método no permitido.']);
    exit;
}

try {
    $input    = json_decode(file_get_contents('php://input'), true);
    $pregunta = sanitize($input['pregunta'] ?? '');

    if (strlen($pregunta) < 2) {
        echo json_encode(['success' => false, 'respuesta' => 'Escribe tu pregunta.']);
        exit;
    }

    // ── Paso 1: Respuestas instantáneas ──
    $rapida = respuestaRapida($pregunta);
    if ($rapida) {
        echo json_encode(['success' => true, 'respuesta' => $rapida, 'fuente' => 'rapida']);
        exit;
    }

    // ── Paso 2: Obtener contexto completo de la BD ──
    $ctx = obtenerContextoBD($pregunta);

    // ── Paso 3: IA como motor principal ──
    $systemPrompt = construirSystemPrompt($ctx);
    $iaResult     = llamarIA($pregunta, $systemPrompt);

    if ($iaResult['ok']) {
        echo json_encode([
            'success'   => true,
            'respuesta' => markdownAHTML($iaResult['texto']),
            'fuente'    => 'ia',
        ]);
        exit;
    }

    // ── Paso 4: Fallback local si la IA falla ──
    error_log("[El Zapato Chatbot] IA no disponible ({$iaResult['error']}), usando fallback local.");
    echo json_encode([
        'success'   => true,
        'respuesta' => fallbackLocal($ctx),
        'fuente'    => 'local',
    ]);

} catch (Throwable $e) {
    error_log("[El Zapato Chatbot] Excepción: " . $e->getMessage());
    echo json_encode([
        'success'   => false,
        'respuesta' => '❌ Error interno. Por favor intenta de nuevo.',
    ]);
}