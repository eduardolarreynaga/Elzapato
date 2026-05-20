<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../model/conexion.php';

function sanitizeInput($input) {
    return trim(htmlspecialchars(strip_tags($input)));
}

function getConnection() {
    return Conexion::conectar();
}

// Función para normalizar texto (tolerante a errores)
function normalizeText($text) {
    $text = strtolower($text);
    // Remover tildes
    $text = preg_replace('/[áä]/u', 'a', $text);
    $text = preg_replace('/[éë]/u', 'e', $text);
    $text = preg_replace('/[íï]/u', 'i', $text);
    $text = preg_replace('/[óö]/u', 'o', $text);
    $text = preg_replace('/[úü]/u', 'u', $text);
    // Remover caracteres especiales y números sueltos
    $text = preg_replace('/[^a-z\s]/u', '', $text);
    // Reemplazar múltiples espacios por uno solo
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

// Diccionario de palabras coloquiales salvadoreñas
$colloquialMap = [
    // Saludos y expresiones
    'que onda' => 'hola',
    'que pedo' => 'hola',
    'que tal' => 'hola',
    'como estas' => 'hola',
    'buenas' => 'hola',
    'maje' => 'amigo',
    'cerote' => 'amigo',
    'bicho' => 'joven',
    'chivo' => 'bueno',
    'pisto' => 'dinero',
    'chumpa' => 'chaqueta',
    
    // Preguntas comunes mal escritas
    'donde esta' => 'ubicacion',
    'donde queda' => 'ubicacion',
    'como llegar' => 'ubicacion',
    'que vende' => 'que_venden',
    'que venden' => 'que_venden',
    'que ofrecen' => 'que_venden',
    'que hay' => 'que_venden',
    'que horario' => 'horarios',
    'a que hora' => 'horarios',
    'cuando abren' => 'horarios',
    'cuando cierran' => 'horarios',
    'como pagar' => 'pagos',
    'forma de pago' => 'pagos',
    'numero de telefono' => 'contacto',
    'como contacto' => 'contacto',
    'envian a casa' => 'envios',
    'envian a domicilio' => 'envios',
    'quienes son' => 'nosotros',
    'cuenten de ustedes' => 'nosotros',
    'que marca' => 'marcas',
    'que marcas' => 'marcas',
    'cual es el precio' => 'precios',
    'cuanto vale' => 'precios',
    'cuanto esta' => 'precios',
    'hay stock' => 'stock',
    'tiene disponible' => 'stock',
    'que talla' => 'tallas',
    'tiene color' => 'colores'
];

// Función para traducir lenguaje coloquial
function translateColloquial($text) {
    global $colloquialMap;
    foreach ($colloquialMap as $colq => $translation) {
        if (strpos($text, $colq) !== false) {
            $text = str_replace($colq, $translation, $text);
        }
    }
    return $text;
}

// Función para detectar si la pregunta tiene sentido (mínimo 3 caracteres significativos)
function isValidQuestion($text) {
    $commonWords = ['hola', 'buenas', 'que', 'como', 'donde', 'cuando', 'porque', 'para que', 'cual', 'quien'];
    $words = explode(' ', $text);
    $meaningful = 0;
    foreach ($words as $word) {
        if (strlen($word) > 2 && !in_array($word, $commonWords)) {
            $meaningful++;
        }
    }
    return $meaningful >= 1 || strpos($text, 'hola') !== false || strpos($text, 'buenas') !== false;
}

// Recibir la pregunta
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
$pregunta = sanitizeInput($data['pregunta'] ?? '');

if (empty($pregunta)) {
    echo json_encode(['respuesta' => 'Por favor, escribe una pregunta.']);
    exit;
}

// Normalizar y procesar
$originalQ = $pregunta;
$q = normalizeText($originalQ);
$q = translateColloquial($q);

// ====================== VERIFICAR SI ES PREGUNTA VÁLIDA ======================
if (!isValidQuestion($q) || strlen($q) < 3) {
    echo json_encode(['respuesta' => '😓 Lo sentimos, no contamos con la información que solicitaste.']);
    exit;
}

// ====================== SALUDOS Y EXPRESIONES COLOQUIALES ======================
if (preg_match('/^(hola|buenas|que onda|que tal|saludos|hey|holi)/i', $q)) {
    echo json_encode(['respuesta' => '👋 ¡Hola! ¿En qué puedo ayudarte hoy? Puedes preguntarme sobre horarios, ubicación, productos, marcas y más.']);
    exit;
}

if (preg_match('/gracias|merci|thx|thanks/i', $q)) {
    echo json_encode(['respuesta' => '🙌 ¡De nada! Estoy aquí para ayudarte. ¿Necesitas algo más?']);
    exit;
}

if (preg_match('/adios|chao|bye|hasta luego|nos vemos/i', $q)) {
    echo json_encode(['respuesta' => '👋 ¡Hasta luego! Que tengas un excelente día. ¡Vuelve pronto!']);
    exit;
}

if (preg_match('/como estas|como andas|que tal estas|que pedo|que onda contigo/i', $q)) {
    echo json_encode(['respuesta' => '🤖 ¡Todo bien por aquí! Listo para ayudarte con lo que necesites sobre ElZapato.']);
    exit;
}

// ====================== DETECCIÓN DE INTENCIÓN MEJORADA ======================
$intent = null;
$params = [];

// Palabras clave para cada intención (orden de prioridad)
$intentsMap = [
    'que_venden' => [
        'keywords' => ['que venden', 'que ofrecen', 'que productos tienen', 'que hay', 'que clase de zapatos', 'que tipos', 'que vende', 'que comercializan', 'que oferta', 'que tienen', 'que manejan'],
        'priority' => 1
    ],
    'horarios' => [
        'keywords' => ['horario', 'hora', 'abren', 'cierran', 'atencion', 'cuando abren', 'cuando cierran', 'que horario', 'horas de atencion', 'a que hora abren', 'a que hora cierran'],
        'priority' => 2
    ],
    'ubicacion' => [
        'keywords' => ['ubicacion', 'donde queda', 'direccion', 'sucursal', 'lugar', 'local', 'como llegar', 'mapa', 'waze', 'google maps', 'donde esta', 'donde estan', 'en que calle'],
        'priority' => 2
    ],
    'pagos' => [
        'keywords' => ['pago', 'pagar', 'tarjeta', 'efectivo', 'credito', 'debito', 'transferencia', 'metodo de pago', 'como pago', 'formas de pago', 'con que se paga'],
        'priority' => 2
    ],
    'contacto' => [
        'keywords' => ['contacto', 'telefono', 'correo', 'email', 'llamar', 'whatsapp', 'comunicarse', 'numero', 'telefonico', 'llamada', 'numero de telefono'],
        'priority' => 2
    ],
    'envios' => [
        'keywords' => ['envio', 'domicilio', 'delivery', 'entregas', 'envian', 'envios a domicilio', 'envian a casa', 'mandan a casa', 'traen a casa'],
        'priority' => 2
    ],
    'nosotros' => [
        'keywords' => ['nosotros', 'historia', 'quienes somos', 'mision', 'vision', 'valores', 'sobre la empresa', 'quienes son', 'cuenten de ustedes'],
        'priority' => 2
    ],
    'categorias' => [
        'keywords' => ['categorias', 'tipos', 'clases', 'tipos de zapatos', 'clases de calzado', 'variedad', 'lineas', 'estilos', 'que clase', 'que tipos'],
        'priority' => 1
    ],
    'marcas' => [
        'keywords' => ['marcas', 'que marcas', 'que marca', 'marcas disponibles', 'marcas venden', 'que marcas tienen', 'cuales marcas'],
        'priority' => 1
    ],
    'precios_generales' => [
        'keywords' => ['precio promedio', 'rango de precios', 'precios generales', 'cuanto cuesta un zapato', 'que precio tienen', 'costos', 'cuanto cuestan aproximadamente'],
        'priority' => 2
    ],
    'tallas' => [
        'keywords' => ['talla', 'tallas', 'que tallas', 'tallas disponibles', 'numeracion', 'que numero', 'que talla manejan'],
        'priority' => 2
    ],
    'colores' => [
        'keywords' => ['color', 'colores', 'que colores', 'colores disponibles', 'de que color', 'tonos'],
        'priority' => 2
    ],
    'productos_especificos' => [
        'keywords' => ['zapato', 'modelo', 'producto', 'calzado', 'tienen de', 'busco', 'necesito', 'quiero comprar', 'precio de', 'cuanto cuesta el', 'cuesta el', 'un zapato', 'un par', 'zapatos de'],
        'priority' => 3
    ],
    'stock' => [
        'keywords' => ['stock', 'disponible', 'queda', 'inventario', 'hay stock', 'tienen disponible', 'en existencia', 'agotado', 'cuantos quedan'],
        'priority' => 3
    ]
];

// Detectar la intención
foreach ($intentsMap as $intentName => $config) {
    foreach ($config['keywords'] as $keyword) {
        if (strpos($q, $keyword) !== false) {
            $intent = $intentName;
            break 2;
        }
    }
}

// Si no se detectó intención, buscar por palabras sueltas
if (!$intent) {
    if (preg_match('/\b(nike|adidas|puma|converse|vans|skechers|new balance|reebok|newbalance)\b/i', $originalQ)) {
        $intent = 'productos_especificos';
        preg_match('/\b(nike|adidas|puma|converse|vans|skechers|new balance|reebok|newbalance)\b/i', $originalQ, $match);
        $params['marca'] = strtolower(str_replace(' ', '', $match[0]));
    }
    elseif (preg_match('/\b(3[5-9]|[4-4][0-9]|50)\b/', $originalQ, $match)) {
        $intent = 'productos_especificos';
        $params['talla'] = $match[0];
    }
    elseif (preg_match('/\b(negro|blanco|rojo|azul|gris|verde|cafe|marron|beige|rosado|amarillo|morado)\b/i', $originalQ, $match)) {
        $intent = 'productos_especificos';
        $params['color'] = strtolower($match[0]);
    }
    else {
        $intent = 'no_encontrado';
    }
}

// ====================== PROCESAR SEGÚN INTENCIÓN ======================
$respuesta = '';

switch ($intent) {
    case 'que_venden':
        $conn = getConnection();
        $sql = "SELECT nombre_categoria FROM categorias ORDER BY id_categoria";
        $stmt = $conn->query($sql);
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $respuesta = "<i class='bi bi-grid-fill text-accent'></i> <strong>👟 En ElZapato vendemos:</strong><br><br>";
        foreach ($categorias as $cat) {
            $respuesta .= "• <strong>{$cat['nombre_categoria']}</strong><br>";
        }
        break;
    
    case 'categorias':
        $conn = getConnection();
        $sql = "SELECT nombre_categoria FROM categorias ORDER BY id_categoria";
        $stmt = $conn->query($sql);
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $respuesta = "<i class='bi bi-grid-fill text-accent'></i> <strong>📂 Tipos de calzado disponibles:</strong><br><br>";
        foreach ($categorias as $cat) {
            $respuesta .= "• {$cat['nombre_categoria']}<br>";
        }
        break;
    
    case 'marcas':
        $conn = getConnection();
        $sql = "SELECT nombre_marca FROM marcas ORDER BY nombre_marca";
        $stmt = $conn->query($sql);
        $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $respuesta = "<i class='bi bi-tags-fill text-accent'></i> <strong>🏷️ Marcas disponibles:</strong><br><br>";
        foreach ($marcas as $marca) {
            $respuesta .= "• {$marca['nombre_marca']}<br>";
        }
        break;
    
    case 'tallas':
        $conn = getConnection();
        $sql = "SELECT DISTINCT talla FROM producto_variante WHERE estado = 'activo' ORDER BY CAST(talla AS UNSIGNED)";
        $stmt = $conn->query($sql);
        $tallas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $respuesta = "<i class='bi bi-rulers text-accent'></i> <strong>📏 Tallas disponibles:</strong><br><br>";
        foreach ($tallas as $t) {
            $respuesta .= "• Talla {$t['talla']}<br>";
        }
        break;
    
    case 'colores':
        $conn = getConnection();
        $sql = "SELECT DISTINCT color FROM producto_variante WHERE estado = 'activo' AND color IS NOT NULL ORDER BY color";
        $stmt = $conn->query($sql);
        $colores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $respuesta = "<i class='bi bi-palette-fill text-accent'></i> <strong>🎨 Colores disponibles:</strong><br><br>";
        foreach ($colores as $c) {
            $respuesta .= "• " . ucfirst($c['color']) . "<br>";
        }
        break;
    
    case 'productos_especificos':
        $conn = getConnection();
        
        $sql = "SELECT DISTINCT p.nombre_producto, m.nombre_marca, c.nombre_categoria,
                       pv.talla, pv.color, pv.precio_venta, pv.stock
                FROM productos p
                JOIN marcas m ON p.id_marca = m.id_marca
                JOIN categorias c ON p.id_categoria = c.id_categoria
                JOIN producto_variante pv ON p.id_producto = pv.id_producto
                WHERE p.estado = 'activo' AND pv.stock > 0";
        
        $hasFilter = false;
        
        if (isset($params['marca'])) {
            $sql .= " AND LOWER(m.nombre_marca) = :marca";
            $hasFilter = true;
        }
        if (isset($params['talla'])) {
            $sql .= " AND pv.talla = :talla";
            $hasFilter = true;
        }
        if (isset($params['color'])) {
            $sql .= " AND LOWER(pv.color) = :color";
            $hasFilter = true;
        }
        
        if ($hasFilter) {
            $sql .= " LIMIT 5";
            $stmt = $conn->prepare($sql);
            if (isset($params['marca'])) $stmt->bindValue(':marca', $params['marca']);
            if (isset($params['talla'])) $stmt->bindValue(':talla', $params['talla']);
            if (isset($params['color'])) $stmt->bindValue(':color', $params['color']);
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($productos)) {
                $respuesta = "<i class='bi bi-search text-accent'></i> <strong>🔍 Productos encontrados:</strong><br><br>";
                foreach ($productos as $prod) {
                    $stockTexto = ($prod['stock'] > 0) ? "✅ Stock: {$prod['stock']}" : "❌ Agotado";
                    $respuesta .= "• <strong>{$prod['nombre_producto']}</strong><br>";
                    $respuesta .= "  {$prod['nombre_marca']} - {$prod['nombre_categoria']}<br>";
                    $respuesta .= "  💰 \${$prod['precio_venta']} | Talla {$prod['talla']} | Color: {$prod['color']}<br>";
                    $respuesta .= "  {$stockTexto}<br><br>";
                }
            } else {
                $busqueda = "";
                if (isset($params['marca'])) $busqueda .= " de la marca " . ucfirst($params['marca']);
                if (isset($params['talla'])) $busqueda .= " en talla {$params['talla']}";
                if (isset($params['color'])) $busqueda .= " de color {$params['color']}";
                
                $respuesta = "😓 Lo sentimos, no contamos con la información que solicitaste.";
            }
        } else {
            // Si preguntó por productos sin filtros, mostrar categorías
            $sqlCat = "SELECT nombre_categoria FROM categorias ORDER BY id_categoria";
            $stmtCat = $conn->query($sqlCat);
            $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
            
            $respuesta = "<i class='bi bi-grid-fill text-accent'></i> <strong>👟 En ElZapato vendemos:</strong><br><br>";
            foreach ($categorias as $cat) {
                $respuesta .= "• <strong>{$cat['nombre_categoria']}</strong><br>";
            }
            $respuesta .= "<br><br>💡 ¿Buscas algo específico? Dime la marca, talla o color que te interesa.";
        }
        break;
    
    case 'precios_generales':
        $conn = getConnection();
        $sql = "SELECT MIN(precio_venta) as min, MAX(precio_venta) as max FROM producto_variante WHERE estado = 'activo'";
        $stmt = $conn->query($sql);
        $precios = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $respuesta = "<i class='bi bi-currency-dollar text-accent'></i> <strong>💰 Rango de precios:</strong><br><br>
                      • Precio mínimo: $" . number_format($precios['min'], 2) . "<br>
                      • Precio máximo: $" . number_format($precios['max'], 2);
        break;
    
    case 'horarios':
        $respuesta = "<i class='bi bi-clock-fill text-accent'></i> <strong>🕒 Horarios:</strong><br><br>
                      • Lunes a Sábado: 8:00 AM - 5:00 PM<br>
                      • Domingos: 8:00 AM - 12:00 PM";
        break;
    
    case 'ubicacion':
        $respuesta = "<i class='bi bi-geo-alt-fill text-accent'></i> <strong>📍 Ubicación:</strong><br><br>
                      <strong>Km 51, Cantón Agua Zarca, Ilobasco, El Salvador</strong><br><br>
                      <i class='fab fa-waze'></i> <a href='https://waze.com/ul?ll=13.8152576,-88.8626189&navigate=yes' target='_blank' style='color: var(--nocolor);'>Abrir en Waze</a> | 
                      <a href='https://maps.google.com/?q=13.8152576,-88.8626189' target='_blank' style='color: var(--nocolor);'>Google Maps</a>";
        break;
    
    case 'pagos':
        $respuesta = "<i class='bi bi-credit-card-2-front-fill text-accent'></i> <strong>💳 Métodos de pago:</strong><br><br>
                      • Efectivo<br>
                      • Tarjeta de Débito/Crédito (Visa, MasterCard)<br>
                      • Transferencia bancaria";
        break;
    
    case 'contacto':
        $respuesta = "<i class='bi bi-telephone-fill text-accent'></i> <strong>📞 Contacto:</strong><br><br>
                      • Teléfono: (503) 2378-1500<br>
                      • Email: contacto@elzapato.com";
        break;
    
    case 'envios':
        $respuesta = "<i class='bi bi-truck text-accent'></i> <strong>🚚 Envíos:</strong><br><br>
                      ⚠️ No realizamos envíos a domicilio. Las compras son solo en tienda física.";
        break;
    
    case 'nosotros':
        $respuesta = "<i class='bi bi-buildings-fill text-accent'></i> <strong>👟 Sobre ElZapato:</strong><br><br>
                      Zapatería salvadoreña con más de 12 años de experiencia. Calidad y estilo para toda la familia.";
        break;
    
    case 'stock':
        $conn = getConnection();
        $sql = "SELECT SUM(stock) as total FROM producto_variante WHERE estado = 'activo'";
        $stmt = $conn->query($sql);
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $respuesta = "<i class='bi bi-box-seam-fill text-accent'></i> <strong>📦 Stock general:</strong><br><br>
                      Contamos con un amplio inventario de calzado. Para consultar disponibilidad de un modelo específico, indícame la marca y talla.";
        break;
    
    case 'no_encontrado':
    default:
        $respuesta = "😓 Lo sentimos, no contamos con la información que solicitaste.";
        break;
}

echo json_encode(['respuesta' => $respuesta]);
?>