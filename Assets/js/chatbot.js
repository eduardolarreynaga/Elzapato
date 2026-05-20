// Variables globales
let optionsEnabled = true;
let timeoutId = null;

// Abrir/Cerrar Ventana
window.toggleChatbot = function() {
    const container = document.getElementById('chatbot-container');
    if (container) {
        container.classList.toggle('chatbot-hidden');
    }
};

// Respuestas estáticas (para preguntas rápidas offline)
const respuestasStatic = {
    1: "<i class='bi bi-geo-alt-fill text-accent'></i> <strong>📍 Ubicación de ElZapato:</strong><br>Km 51, Cantón Agua Zarca, Ilobasco.<br><br><a href='https://waze.com/ul?ll=13.8152576,-88.8626189&navigate=yes' target='_blank'><i class='fab fa-waze'></i> Abrir en Waze</a> | <a href='https://maps.google.com/?q=13.8152576,-88.8626189' target='_blank'>Google Maps</a>",
    
    2: "<i class='bi bi-clock-fill text-accent'></i> <strong>🕒 Horarios:</strong><br>Lunes a Sábado: 8am-5pm<br>Domingos: 8am-12pm",
    
    3: "<i class='bi bi-credit-card-2-front-fill text-accent'></i> <strong>💳 Pagos:</strong><br>Efectivo, Tarjeta (Visa/MasterCard) y Transferencia",
    
    4: "<i class='bi bi-layers-fill text-accent'></i> <strong>👟 Tipos de calzado:</strong><br>Deportivos, Casuales, Formales, Sandalias, Botas, Urbanos",
    
    5: "<i class='bi bi-patch-check-fill text-accent'></i> <strong>🔥 Marcas:</strong><br>Nike, Adidas, Puma, Converse, Vans, Skechers, New Balance, Reebok",
    
    6: "<i class='bi bi-exclamation-triangle-fill text-accent'></i> <strong>🚚 Envíos:</strong><br>No realizamos envíos. Solo ventas en tienda física.",
    
    7: "<i class='bi bi-telephone-fill text-accent'></i> <strong>📞 Contacto:</strong><br>Tel: (503) 2378-1500<br>Email: contacto@elzapato.com"
};

// Función para agregar mensaje al chat
function addMessageToChat(texto, clase) {
    const messagesBody = document.getElementById('chatbot-messages');
    if (!messagesBody) return;
    
    const div = document.createElement('div');
    div.classList.add('message', clase);
    div.innerHTML = texto;
    messagesBody.appendChild(div);
    messagesBody.scrollTop = messagesBody.scrollHeight;
}

// Mostrar/ocultar indicador de escritura
function showTypingIndicator(show) {
    const typingIndicator = document.getElementById('chatbot-typing-indicator');
    if (!typingIndicator) return;
    
    if (show) {
        typingIndicator.classList.remove('typing-hidden');
    } else {
        typingIndicator.classList.add('typing-hidden');
    }
    
    const messagesBody = document.getElementById('chatbot-messages');
    if (messagesBody) messagesBody.scrollTop = messagesBody.scrollHeight;
}

// Habilitar/deshabilitar botones predefinidos
function toggleButtons(enable) {
    optionsEnabled = enable;
    const buttons = document.querySelectorAll('.chat-option-btn');
    buttons.forEach(button => {
        button.disabled = !enable;
    });
}

// Enviar pregunta desde botones predefinidos
window.sendPregunta = function(idPregunta, textoPregunta) {
    if (!optionsEnabled) return;
    
    addMessageToChat(textoPregunta, 'user-message');
    toggleButtons(false);
    showTypingIndicator(true);
    
    if (timeoutId) clearTimeout(timeoutId);
    
    timeoutId = setTimeout(() => {
        showTypingIndicator(false);
        const respuesta = respuestasStatic[idPregunta] || "Lo siento, no tengo una respuesta para esa opción.";
        addMessageToChat(respuesta, 'bot-message');
        toggleButtons(true);
        timeoutId = null;
    }, 800);
};

// Enviar mensaje de texto libre al backend PHP
async function sendTextMessage() {
    const input = document.getElementById('user-input');
    if (!input) return;
    
    const messageText = input.value.trim();
    if (messageText === "") return;
    
    // Mostrar mensaje del usuario
    addMessageToChat(messageText, 'user-message');
    input.value = "";
    
    // Deshabilitar botones mientras se procesa
    toggleButtons(false);
    showTypingIndicator(true);
    
    try {
        // Llamar al endpoint PHP
        const response = await fetch('/ElZapato/src/api/chatbot-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ pregunta: messageText })
        });
        
        const data = await response.json();
        
        if (timeoutId) clearTimeout(timeoutId);
        
        timeoutId = setTimeout(() => {
            showTypingIndicator(false);
            addMessageToChat(data.respuesta, 'bot-message');
            toggleButtons(true);
            timeoutId = null;
        }, 300);
        
    } catch (error) {
        console.error('Error:', error);
        showTypingIndicator(false);
        addMessageToChat("⚠️ Lo siento, hubo un error de conexión. Intenta de nuevo.", 'bot-message');
        toggleButtons(true);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    const sendButton = document.getElementById('send-btn');
    const userInput = document.getElementById('user-input');
    
    if (sendButton) {
        sendButton.addEventListener('click', sendTextMessage);
        console.log('✅ Botón enviar configurado correctamente');
    } else {
        console.error('❌ No se encontró #send-btn');
    }
    
    if (userInput) {
        userInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendTextMessage();
            }
        });
        console.log('✅ Input configurado correctamente');
    }
});

console.log('🚀 Chatbot Inteligente de ElZapato cargado');