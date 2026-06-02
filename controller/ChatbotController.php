<?php
/**
 * Controlador del Chatbot Inteligente
 * 
 * Orquesta la comunicación entre la API y el modelo de conocimiento.
 * Mantiene compatibilidad con la estructura MVC existente.
 */

require_once __DIR__ . '/../model/ChatbotModel.php';

class ChatbotController
{
    private ChatbotModel $modelo;

    public function __construct()
    {
        $this->modelo = new ChatbotModel();
    }

    /**
     * Procesa una pregunta del usuario y devuelve la respuesta más relevante
     * 
     * @param string $pregunta La pregunta del usuario
     * @param string $sesionId ID único de sesión del navegador
     * @param int|null $usuarioId ID del usuario si está logueado (opcional)
     * @return array Respuesta formateada para el frontend
     */
    public function procesarPregunta(
        string $pregunta,
        string $sesionId = '',
        ?int $usuarioId = null
    ): array {
        // 1. Guardar la pregunta del usuario en el historial
        if (!empty($sesionId)) {
            $this->modelo->guardarMensaje($usuarioId, $sesionId, 'usuario', $pregunta);
        }

        // 2. Buscar la respuesta más relevante
        $resultado = $this->modelo->buscarRespuestaRelevante($pregunta);

        // 3. Construir la respuesta estructurada
        $respuestaFinal = [
            'success' => true,
            'respuesta' => $resultado['respuesta'],
            'score' => round($resultado['score'], 3),
            'fuente' => $resultado['fuente'],
            'es_baja_confianza' => $resultado['score'] < 0.25
        ];

        // 4. Guardar la respuesta del asistente en el historial
        if (!empty($sesionId)) {
            $metadataAsistente = [
                'score' => $resultado['score'],
                'fuente' => $resultado['fuente']
            ];
            $this->modelo->guardarMensaje(
                $usuarioId,
                $sesionId,
                'asistente',
                $respuestaFinal['respuesta'],
                $metadataAsistente
            );
        }

        return $respuestaFinal;
    }
}