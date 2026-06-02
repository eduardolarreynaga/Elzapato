<?php
require_once __DIR__ . '/conexion.php';

class ChatbotModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::conectar();
    }

    public function buscarRespuestaRelevante(string $preguntaOriginal): array
    {
        $pregunta = mb_strtolower(trim($preguntaOriginal), 'UTF-8');
        $textoLimpio = $this->limpiarTexto($pregunta);
        $palabras = array_filter(explode(' ', $textoLimpio), fn($p) => strlen($p) > 1);

        if (empty($palabras)) {
            return $this->respuestaPorDefecto();
        }

        // 1. SIEMPRE buscar primero en productos reales
        $resultado = $this->buscarEnProductosReales($palabras, $pregunta);
        if ($resultado['score'] > 0) {
            return $resultado;
        }

        // 2. Buscar en knowledge_base SOLO para info institucional
        $resultadoInfo = $this->buscarInfoInstitucional($pregunta);
        if ($resultadoInfo['score'] > 0) {
            return $resultadoInfo;
        }

        // 3. Búsqueda flexible por categoría
        $resultadoCat = $this->buscarPorCategoria($pregunta);
        if ($resultadoCat['score'] > 0) {
            return $resultadoCat;
        }

        // 4. Búsqueda flexible por marca
        $resultadoMarca = $this->buscarPorMarca($pregunta);
        if ($resultadoMarca['score'] > 0) {
            return $resultadoMarca;
        }

        // 5. Productos más vendidos si pregunta por popularidad
        if (preg_match('/mas vendido|popular|top|mejor|recomendado|destacado|favorito/', $pregunta)) {
            return $this->productosPopulares();
        }

        return $this->respuestaPorDefecto();
    }

    private function buscarEnProductosReales(array $palabras, string $preguntaOriginal): array
    {
        try {
            $condiciones = [];
            $params = [];

            foreach ($palabras as $i => $palabra) {
                $p = ":p{$i}";
                $condiciones[] = "(p.nombre_producto LIKE {$p} OR p.descripcion LIKE {$p} OR m.nombre_marca LIKE {$p} OR c.nombre_categoria LIKE {$p} OR pv.color LIKE {$p} OR pv.talla LIKE {$p})";
                $params[$p] = "%{$palabra}%";
            }

            $sql = "SELECT p.id_producto, p.nombre_producto, p.descripcion, 
                           c.nombre_categoria, m.nombre_marca,
                           MIN(pv.precio_venta) as precio_min,
                           MAX(pv.precio_venta) as precio_max,
                           SUM(pv.stock) as stock_total,
                           GROUP_CONCAT(DISTINCT pv.talla ORDER BY CAST(pv.talla AS UNSIGNED) SEPARATOR ', ') as tallas,
                           GROUP_CONCAT(DISTINCT pv.color ORDER BY pv.color SEPARATOR ', ') as colores
                    FROM productos p
                    JOIN categorias c ON p.id_categoria = c.id_categoria
                    JOIN marcas m ON p.id_marca = m.id_marca
                    JOIN producto_variante pv ON p.id_producto = pv.id_producto AND pv.estado = 'activo'
                    WHERE p.estado = 'activo' 
                      AND (" . implode(' OR ', $condiciones) . ")
                    GROUP BY p.id_producto
                    ORDER BY 
                        (CASE WHEN m.nombre_marca LIKE :exacta THEN 1 ELSE 0 END) DESC,
                        stock_total DESC
                    LIMIT 5";

            $params['exacta'] = "%{$palabras[0]}%";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($productos) > 0) {
                return [
                    'respuesta' => $this->formatearProductos($productos, $preguntaOriginal),
                    'score' => 0.7,
                    'fuente' => 'productos_reales'
                ];
            }
        } catch (PDOException $e) {
            error_log("Error búsqueda: " . $e->getMessage());
        }

        return ['respuesta' => '', 'score' => 0, 'fuente' => ''];
    }

    private function buscarPorCategoria(string $pregunta): array
    {
        try {
            // Buscar si alguna categoría coincide
            $stmt = $this->db->query("SELECT id_categoria, nombre_categoria FROM categorias");
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($categorias as $cat) {
                $nombre = $this->limpiarTexto($cat['nombre_categoria']);
                if (strpos($pregunta, $nombre) !== false || 
                    preg_match('/' . preg_quote($nombre, '/') . '/', $this->limpiarTexto($pregunta))) {
                    
                    // Encontró categoría, buscar productos
                    $sql = "SELECT p.nombre_producto, p.descripcion, c.nombre_categoria, m.nombre_marca,
                                   MIN(pv.precio_venta) as precio_min, MAX(pv.precio_venta) as precio_max,
                                   SUM(pv.stock) as stock_total
                            FROM productos p
                            JOIN categorias c ON p.id_categoria = c.id_categoria
                            JOIN marcas m ON p.id_marca = m.id_marca
                            JOIN producto_variante pv ON p.id_producto = pv.id_producto AND pv.estado = 'activo'
                            WHERE p.estado = 'activo' AND c.id_categoria = :cat_id
                            GROUP BY p.id_producto
                            ORDER BY stock_total DESC
                            LIMIT 5";
                    
                    $stmt2 = $this->db->prepare($sql);
                    $stmt2->execute(['cat_id' => $cat['id_categoria']]);
                    $productos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

                    if (count($productos) > 0) {
                        return [
                            'respuesta' => "📂 <strong>{$cat['nombre_categoria']} disponibles:</strong><br><br>" . 
                                          $this->formatearProductos($productos, ''),
                            'score' => 0.8,
                            'fuente' => 'categoria'
                        ];
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Error categoría: " . $e->getMessage());
        }

        return ['respuesta' => '', 'score' => 0, 'fuente' => ''];
    }

    private function buscarPorMarca(string $pregunta): array
    {
        try {
            $stmt = $this->db->query("SELECT id_marca, nombre_marca FROM marcas");
            $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($marcas as $marca) {
                $nombre = $this->limpiarTexto($marca['nombre_marca']);
                if (strpos($pregunta, $nombre) !== false) {
                    
                    $sql = "SELECT p.nombre_producto, p.descripcion, c.nombre_categoria, m.nombre_marca,
                                   MIN(pv.precio_venta) as precio_min, MAX(pv.precio_venta) as precio_max,
                                   SUM(pv.stock) as stock_total
                            FROM productos p
                            JOIN categorias c ON p.id_categoria = c.id_categoria
                            JOIN marcas m ON p.id_marca = m.id_marca
                            JOIN producto_variante pv ON p.id_producto = pv.id_producto AND pv.estado = 'activo'
                            WHERE p.estado = 'activo' AND m.id_marca = :marca_id
                            GROUP BY p.id_producto
                            ORDER BY stock_total DESC";
                    
                    $stmt2 = $this->db->prepare($sql);
                    $stmt2->execute(['marca_id' => $marca['id_marca']]);
                    $productos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

                    if (count($productos) > 0) {
                        return [
                            'respuesta' => "🏷️ <strong>{$marca['nombre_marca']}:</strong><br><br>" . 
                                          $this->formatearProductos($productos, ''),
                            'score' => 0.8,
                            'fuente' => 'marca'
                        ];
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Error marca: " . $e->getMessage());
        }

        return ['respuesta' => '', 'score' => 0, 'fuente' => ''];
    }

    private function buscarInfoInstitucional(string $pregunta): array
    {
        // Solo buscar información NO relacionada con productos
        $patrones = [
            'horarios' => '/horario|abren|cierran|abierto|hora|cuando|domingo|sabado|lunes|atencion/',
            'pagos' => '/pago|pagar|tarjeta|efectivo|transferencia|factura|credito|debito|visa|mastercard/',
            'envios' => '/envio|envian|domicilio|delivery|entrega|mandan|llevan/',
            'contacto' => '/contacto|telefono|llamar|whatsapp|email|correo|numero/',
            'ubicacion' => '/ubicacion|donde|direccion|ubicado|mapa|waze|llegar|queda/',
            'devoluciones' => '/devolucion|devolver|cambio|reembolso|garantia|defecto|falla/',
        ];

        foreach ($patrones as $tipo => $patron) {
            if (preg_match($patron, $pregunta)) {
                try {
                    $stmt = $this->db->prepare(
                        "SELECT contenido FROM knowledge_base WHERE activo = 1 AND subcategoria = :tipo LIMIT 1"
                    );
                    $stmt->execute(['tipo' => $tipo]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($row) {
                        return [
                            'respuesta' => $row['contenido'],
                            'score' => 0.9,
                            'fuente' => 'info_institucional'
                        ];
                    }
                } catch (PDOException $e) {
                    error_log("Error info: " . $e->getMessage());
                }
            }
        }

        return ['respuesta' => '', 'score' => 0, 'fuente' => ''];
    }

    private function productosPopulares(): array
    {
        try {
            $sql = "SELECT p.nombre_producto, p.descripcion, c.nombre_categoria, m.nombre_marca,
                           MIN(pv.precio_venta) as precio_min, MAX(pv.precio_venta) as precio_max,
                           SUM(pv.stock) as stock_total,
                           COUNT(dv.id_detalle_venta) as total_vendido
                    FROM productos p
                    JOIN categorias c ON p.id_categoria = c.id_categoria
                    JOIN marcas m ON p.id_marca = m.id_marca
                    JOIN producto_variante pv ON p.id_producto = pv.id_producto
                    LEFT JOIN detalle_venta dv ON pv.id_variante = dv.id_variante
                    WHERE p.estado = 'activo'
                    GROUP BY p.id_producto
                    ORDER BY total_vendido DESC
                    LIMIT 5";
            $stmt = $this->db->query($sql);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($productos) > 0) {
                $respuesta = "🔥 <strong>Productos más populares:</strong><br><br>";
                foreach ($productos as $i => $prod) {
                    $precio = ($prod['precio_min'] == $prod['precio_max'])
                        ? "$" . number_format($prod['precio_min'], 2)
                        : "$" . number_format($prod['precio_min'], 2) . " - $" . number_format($prod['precio_max'], 2);
                    
                    $respuesta .= ($i+1) . ". <strong>{$prod['nombre_producto']}</strong> - {$precio}<br>";
                    $respuesta .= "   {$prod['nombre_marca']} | {$prod['nombre_categoria']} | Vendidos: {$prod['total_vendido']}<br><br>";
                }
                return ['respuesta' => $respuesta, 'score' => 0.8, 'fuente' => 'populares'];
            }
        } catch (PDOException $e) {
            error_log("Error populares: " . $e->getMessage());
        }

        return $this->respuestaPorDefecto();
    }

    private function formatearProductos(array $productos, string $pregunta): string
    {
        $respuesta = "";
        
        foreach ($productos as $prod) {
            $precio = ($prod['precio_min'] == $prod['precio_max'])
                ? "<strong>$" . number_format($prod['precio_min'], 2) . "</strong>"
                : "$" . number_format($prod['precio_min'], 2) . " - $" . number_format($prod['precio_max'], 2);
            
            $stock = ($prod['stock_total'] > 0) ? "✅ {$prod['stock_total']} en stock" : "❌ Agotado";
            
            $respuesta .= "👟 <strong>{$prod['nombre_producto']}</strong><br>";
            $respuesta .= "   🏷️ {$prod['nombre_marca']} | {$prod['nombre_categoria']}<br>";
            $respuesta .= "   💰 {$precio}<br>";
            $respuesta .= "   {$stock}<br>";
            if (!empty($prod['tallas'])) $respuesta .= "   📏 Tallas: {$prod['tallas']}<br>";
            if (!empty($prod['colores'])) $respuesta .= "   🎨 Colores: {$prod['colores']}<br>";
            $respuesta .= "<br>";
        }
        
        return $respuesta;
    }

    private function respuestaPorDefecto(): array
    {
        return [
            'respuesta' => "No encontré resultados. Intenta con:<br>• Nombre de marca: Nike, Adidas, Puma...<br>• Tipo: deportivos, casuales, formales...<br>• O pregunta por: horarios, pagos, contacto, ubicación",
            'score' => 0,
            'fuente' => 'sistema'
        ];
    }

    private function limpiarTexto(string $texto): string
    {
        $texto = preg_replace('/[áä]/u', 'a', $texto);
        $texto = preg_replace('/[éë]/u', 'e', $texto);
        $texto = preg_replace('/[íï]/u', 'i', $texto);
        $texto = preg_replace('/[óö]/u', 'o', $texto);
        $texto = preg_replace('/[úü]/u', 'u', $texto);
        $texto = preg_replace('/[ñ]/u', 'n', $texto);
        $texto = preg_replace('/[^a-z0-9\s]/', '', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    }

    public function guardarMensaje(?int $usuarioId, string $sesionId, string $rol, string $mensaje, ?array $metadata = null): void
    {
        try {
            $sql = "INSERT INTO chat_history (id_usuario, sesion_id, rol, mensaje, metadata) 
                    VALUES (:uid, :sid, :rol, :msg, :meta)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'uid' => $usuarioId, 'sid' => $sesionId,
                'rol' => $rol, 'msg' => $mensaje,
                'meta' => $metadata ? json_encode($metadata) : null
            ]);
        } catch (PDOException $e) {}
    }
}