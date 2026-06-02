let optionsEnabled = true;
let timeoutId = null;
let conversationHistory = [];

window.toggleChatbot = function() {
    const container = document.getElementById('chatbot-container');
    if (container) container.classList.toggle('chatbot-hidden');
};

function addMessageToChat(texto, clase) {
    const messagesBody = document.getElementById('chatbot-messages');
    if (!messagesBody) return;
    const div = document.createElement('div');
    div.classList.add('message', clase);
    div.innerHTML = texto;
    messagesBody.appendChild(div);
    messagesBody.scrollTop = messagesBody.scrollHeight;
}

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

function toggleButtons(enable) {
    optionsEnabled = enable;
    const buttons = document.querySelectorAll('.chat-option-btn');
    buttons.forEach(button => button.disabled = !enable);
}

async function sendTextMessage() {
    const input = document.getElementById('user-input');
    if (!input) return;
    
    const messageText = input.value.trim();
    if (messageText === "") return;
    
    addMessageToChat(messageText, 'user-message');
    input.value = "";
    
    conversationHistory.push({ rol: 'user', mensaje: messageText });
    
    toggleButtons(false);
    showTypingIndicator(true);
    
    try {
        const response = await fetch('/ElZapato/src/api/chatbot-api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                pregunta: messageText,
                historial: conversationHistory
            })
        });
        
        const data = await response.json();
        
        if (timeoutId) clearTimeout(timeoutId);
        
        timeoutId = setTimeout(() => {
            showTypingIndicator(false);
            addMessageToChat(data.respuesta, 'bot-message');
            conversationHistory.push({ rol: 'assistant', mensaje: data.respuesta });
            toggleButtons(true);
            timeoutId = null;
        }, 800);
        
    } catch (error) {
        console.error('Error:', error);
        showTypingIndicator(false);
        addMessageToChat("⚠️ Error de conexión. Intenta de nuevo.", 'bot-message');
        toggleButtons(true);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const sendButton = document.getElementById('send-btn');
    const userInput = document.getElementById('user-input');
    
    if (sendButton) {
        sendButton.addEventListener('click', sendTextMessage);
    }
    
    if (userInput) {
        userInput.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                sendTextMessage();
            }
        });
    }
});

console.log('🚀 Chatbot con IA Gratuita cargado');