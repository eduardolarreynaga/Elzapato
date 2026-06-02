<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="/ElZapato/Assets/css/chatbot.css">

<!-- Botón flotante circular con el logo de ElZapato -->
<div id="chatbot-launcher" onclick="toggleChatbot()">
    <div class="launcher-pulse"></div>
    <img src="/ElZapato/Assets/img/logo.original.backup.png" alt="Logo ElZapato" class="launcher-brand-img">
</div>

<!-- Ventana del Chatbot -->
<div id="chatbot-container" class="chatbot-hidden">
    <!-- Encabezado -->
    <div class="chatbot-header">
        <div class="chatbot-profile">
            <div class="chatbot-avatar">
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

    <!-- Cuerpo del chat -->
    <div class="chatbot-body" id="chatbot-messages">
        <div class="message bot-message animate-fade-in">
            <i class="bi bi-hand-thumbs-up-fill text-accent"></i> ¡Hola! Bienvenido al asistente virtual de <strong>ElZapato</strong>. <br><br>
            Puedes escribir tu pregunta abajo:
        </div>
    </div>

    <!-- Indicador de escritura -->
    <div id="chatbot-typing-indicator" class="typing-hidden">
        <div class="typing-bubble">
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </div>

   

    <!-- Área de entrada de texto libre -->
    <div class="chatbot-input-area">
        <input type="text" id="user-input" placeholder="Escribe tu pregunta aquí... Ej: ¿Tienen zapatos Nike?" autocomplete="off">
        <button id="send-btn"><i class="bi bi-send-fill"></i></button>
    </div>
</div>

<script src="/ElZapato/Assets/js/chatbot.js"></script>