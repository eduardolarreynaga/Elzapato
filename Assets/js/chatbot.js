// Abrir/Cerrar Ventana con transiciones fluidas
function toggleChatbot() {
    const chatContainer = document.getElementById('chatbot-container');
    chatContainer.classList.toggle('chatbot-hidden');
}

// Diccionario de Datos del Negocio con formato e íconos Bootstrap inside
const respuestasBot = {
    1: "<i class='bi bi-geo-alt-fill text-accent'></i> <strong>Ubicación de nuestra tienda:</strong><br>Nos encontramos en la siguiente dirección física:<br><strong>Km 51, Cantón Agua Zarca, Ilobasco.</strong><br><br>¡Te esperamos para atenderte en persona!",
    
    2: "<i class='bi bi-clock-fill text-accent'></i> <strong>Nuestros Horarios:</strong><br>Te atendemos con gusto en los siguientes horarios:<br>• <strong>Lunes a Sábado:</strong> de 8:00 AM a 5:00 PM<br>• <strong>Domingos:</strong> de 8:00 AM a 12:00 PM",
    
    3: "<i class='bi bi-credit-card-2-front-fill text-accent'></i> <strong>Métodos de Pago:</strong><br>En nuestra tienda física puedes cancelar en <strong>Efectivo</strong> y también aceptamos <strong>Tarjeta</strong> (contamos con terminal POS en caja para deslizar o insertar tu tarjeta de forma segura).<br><br>⚠️ <em>Nota: No procesamos pagos directos desde este sitio web.</em>",
    
    4: "<i class='bi bi-layers-fill text-accent'></i> <strong>Tipos de Calzado en existencia:</strong><br>Manejamos una amplia variedad de líneas:<br>• Deportivos<br>• Casuales<br>• Formales<br>• Sandalias<br>• Botas<br>• Urbanos",
    
    5: "<i class='bi bi-patch-check-fill text-accent'></i> <strong>Marcas Disponibles:</strong><br>En nuestras vitrinas encontrarás calzado original de:<br>• Nike, Adidas y Puma<br>• Converse y Vans<br>• Skechers, New Balance y Reebok",
    
    6: "<i class='bi bi-exclamation-triangle-fill text-accent'></i> <strong>Envíos y Ventas Web:</strong><br>Por el momento <strong>NO contamos con venta en línea</strong> y tampoco realizamos envíos a domicilio.<br><br>Esta página web funciona exclusivamente como vitrina digital. Todo proceso de compra, prueba de tallas y pago se realiza en nuestra sucursal.",
    
    7: "<i class='bi bi-telephone-fill text-accent'></i> <strong>Información de Contacto:</strong><br>Comunícate directamente a administración:<br>• <strong>Teléfono:</strong> (503) 2378-1500<br>• <strong>Email:</strong> contacto@elzapato.com"
};

// Enviar pregunta seleccionada
function sendPregunta(idPregunta, textoPregunta) {
    const messagesBody = document.getElementById('chatbot-messages');
    const typingIndicator = document.getElementById('chatbot-typing-indicator');

    // 1. Imprimir la burbuja de la pregunta del usuario
    const userDiv = document.createElement('div');
    userDiv.classList.add('message', 'user-message');
    userDiv.innerHTML = textoPregunta;
    messagesBody.appendChild(userDiv);

    // Mover scroll al final del contenido
    messagesBody.scrollTop = messagesBody.scrollHeight;

    // Deshabilitar el menú para evitar clics dobles confuso
    toggleOptions(false);

    // 2. Mostrar indicador de "Escribiendo..." animado abajo
    typingIndicator.classList.remove('typing-hidden');
    messagesBody.scrollTop = messagesBody.scrollHeight;

    // 3. Simular un delay de respuesta realista (1.2 segundos) para ver la animación
    setTimeout(() => {
        // Ocultar indicador de escritura
        typingIndicator.classList.add('typing-hidden');

        // Construir burbuja de respuesta del bot
        const botDiv = document.createElement('div');
        botDiv.classList.add('message', 'bot-message');
        botDiv.innerHTML = respuestasBot[idPregunta] || "Disculpa la inconsistencia, por favor selecciona otra opción.";
        
        messagesBody.appendChild(botDiv);
        
        // Auto-scroll fluido al nuevo mensaje del bot
        messagesBody.scrollTop = messagesBody.scrollHeight;
        
        // Reactivar botones
        toggleOptions(true);
    }, 1200); 
}

function toggleOptions(enable) {
    const buttons = document.querySelectorAll('.chat-option-btn');
    buttons.forEach(button => {
        button.disabled = !enable;
    });
}