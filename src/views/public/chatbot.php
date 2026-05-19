<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="/ElZapato/Assets/css/chatbot.css">

<!-- Botón flotante circular con el logo de ElZapato -->
<div id="chatbot-launcher" onclick="toggleChatbot()">
    <div class="launcher-pulse"></div>
    <!-- Reemplazo de texto e íconos por tu imagen oficial -->
    <img src="/ElZapato/Assets/img/logo.original.backup.png" alt="Logo ElZapato" class="launcher-brand-img">
</div>

<!-- Ventana del Chatbot Llamativa -->
<div id="chatbot-container" class="chatbot-hidden">
    <!-- Encabezado del Chat -->
    <div class="chatbot-header">
        <div class="chatbot-profile">
            <div class="chatbot-avatar">
                <!-- También usamos tu logo en miniatura dentro del chat para mantener consistencia corporativa -->
                <img src="/ElZapato/Assets/img/logo.original.backup.png" alt="Mini Logo" class="avatar-brand-img">
            </div>
            <div class="chatbot-title-area">
                <h4>Asistente ElZapato</h4>
                <p><span class="online-indicator-pulse"></span> En línea ahora</p>
            </div>
        </div>
        <button class="chatbot-close-btn" onclick="toggleChatbot()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Cuerpo del chat donde fluyen los mensajes -->
    <div class="chatbot-body" id="chatbot-messages">
        <div class="message bot-message animate-fade-in">
            <i class="bi bi-hand-thumbs-up-fill text-accent"></i> ¡Hola! Bienvenido al asistente virtual de <strong>ElZapato</strong>. <br><br>
            ¿En qué podemos ayudarte hoy? Selecciona una opción del menú inferior para responderte de inmediato:
        </div>
    </div>

    <!-- Contenedor dinámico de "Escribiendo..." -->
    <div id="chatbot-typing-indicator" class="typing-hidden">
        <div class="typing-bubble">
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </div>

    <!-- Área de Opciones Precargadas con Íconos Bootstrap -->
    <div class="chatbot-options" id="chatbot-options-container">
        <button class="chat-option-btn" onclick="sendPregunta(1, '📍 ¿Dónde están ubicados?')">
            <i class="bi bi-geo-alt-fill"></i> Ubicación de la tienda
        </button>
        <button class="chat-option-btn" onclick="sendPregunta(2, '🕒 ¿Cuáles son sus horarios?')">
            <i class="bi bi-clock-fill"></i> Horarios de atención
        </button>
        <button class="chat-option-btn" onclick="sendPregunta(3, '💳 ¿Qué métodos de pago aceptan?')">
            <i class="bi bi-credit-card-2-front-fill"></i> Métodos de pago
        </button>
        <button class="chat-option-btn" onclick="sendPregunta(4, '👟 ¿Qué tipos de zapatos venden?')">
            <i class="bi bi-layers-fill"></i> Tipos de calzado
        </button>
        <button class="chat-option-btn" onclick="sendPregunta(5, '🔥 ¿Qué marcas tienen disponibles?')">
            <i class="bi bi-patch-check-fill"></i> Marcas disponibles
        </button>
        <button class="chat-option-btn" onclick="sendPregunta(6, '🚚 ¿Hacen envíos a domicilio?')">
            <i class="bi bi-exclamation-triangle-fill"></i> Envíos y ventas en línea
        </button>
        <button class="chat-option-btn" onclick="sendPregunta(7, '📞 ¿Cuál es su contacto?')">
            <i class="bi bi-telephone-fill"></i> Teléfono y Correo
        </button>
    </div>
</div>

<script src="/ElZapato/Assets/js/chatbot.js"></script>