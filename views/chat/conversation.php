<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xat: <?php echo htmlspecialchars($currentConversation['vehicle_name']); ?> - DriveShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .chat-container {
            height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .message {
            margin-bottom: 15px;
            display: flex;
        }
        .message.own {
            justify-content: flex-end;
        }
        .message.other {
            justify-content: flex-start;
        }
        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
        }
        .message.own .message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .message.other .message-bubble {
            background: white;
            color: #333;
            border: 1px solid #e9ecef;
        }
        .message-info {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 5px;
        }
        .message.own .message-info {
            text-align: right;
        }
        .chat-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #dee2e6;
        }
        .typing-indicator {
            display: none;
            padding: 10px 15px;
            color: #6c757d;
            font-style: italic;
        }
        .vehicle-info {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .back-link {
            color: white;
            text-decoration: none;
        }
        .back-link:hover {
            color: #f8f9fa;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../dashboard.php">
                <i class="bi bi-car-front-fill"></i> DriveShare
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../dashboard.php">
                    <i class="bi bi-house"></i> Inici
                </a>
                <a class="nav-link" href="../ver-coches.php">
                    <i class="bi bi-car-front"></i> Vehicles
                </a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>/index.php?controller=chat&action=index">
                    <i class="bi bi-chat-dots"></i> Xat
                </a>
                <a class="nav-link" href="../logout.php">
                    <i class="bi bi-box-arrow-right"></i> Sortir
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Información del vehículo -->
                <div class="vehicle-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <a href="<?php echo BASE_URL; ?>/index.php?controller=chat&action=index" class="back-link me-3">
                                    <i class="bi bi-arrow-left-circle"></i>
                                </a>
                                <div>
                                    <h5 class="mb-1 text-primary">
                                        <i class="bi bi-car-front"></i> 
                                        <?php echo htmlspecialchars($currentConversation['vehicle_name']); ?>
                                    </h5>
                                    <p class="mb-1 text-muted">
                                        <?php echo htmlspecialchars($currentConversation['brand'] . ' ' . $currentConversation['model']); ?>
                                    </p>
                                    <p class="mb-0 small text-muted">
                                        Conversant amb: 
                                        <strong>
                                            <?php 
                                                if ($_SESSION['user_id'] == $currentConversation['owner_id']) {
                                                    echo htmlspecialchars($currentConversation['renter_name']);
                                                } else {
                                                    echo htmlspecialchars($currentConversation['owner_name']);
                                                }
                                            ?>
                                        </strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-success fs-6">
                                <i class="bi bi-chat-dots"></i> Conversa activa
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Contenedor de chat -->
                <div class="card chat-container">
                    <div class="chat-header">
                        <h6 class="mb-0">
                            <i class="bi bi-chat-text"></i> 
                            Xat sobre <?php echo htmlspecialchars($currentConversation['vehicle_name']); ?>
                        </h6>
                        <small class="opacity-75">
                            Conversa iniciada el <?php echo date('d/m/Y', strtotime($currentConversation['created_at'])); ?>
                        </small>
                    </div>

                    <div class="chat-messages" id="chatMessages">
                        <?php if (empty($messages)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-chat-square-text display-4"></i>
                                <p class="mt-3">No hi ha missatges encara</p>
                                <p>Comença la conversa escrivint un missatge a continuació</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'own' : 'other'; ?>">
                                    <div class="message-bubble">
                                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                        <div class="message-info">
                                            <?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="typing-indicator" id="typingIndicator">
                        <i class="bi bi-three-dots"></i> L'altre usuari està escrivint...
                    </div>

                    <div class="chat-input">
                        <form id="messageForm" onsubmit="sendMessage(event)">
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       id="messageInput" 
                                       placeholder="Escriu el teu missatge..." 
                                       required
                                       maxlength="1000">
                                <button class="btn btn-primary" type="submit" id="sendButton">
                                    <i class="bi bi-send"></i> Enviar
                                </button>
                            </div>
                        </form>
                        <small class="text-muted">
                            Prem Enter per enviar • Màxim 1000 caràcters
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const conversationId = <?php echo $conversationId; ?>;
        let lastMessageId = <?php echo !empty($messages) ? end($messages)['id'] : 0; ?>;
        let isTyping = false;

        // Función para enviar mensaje
        function sendMessage(event) {
            event.preventDefault();
            
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message) return;
            
            const sendButton = document.getElementById('sendButton');
            sendButton.disabled = true;
            sendButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviant...';
            
            const formData = new FormData();
            formData.append('conversation_id', conversationId);
            formData.append('message', message);
            
            fetch('<?php echo BASE_URL; ?>/index.php?controller=chat&action=sendMessage', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    addMessageToChat(message, true, new Date());
                    scrollToBottom();
                } else {
                    alert('Error al enviar mensaje: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error de conexión');
            })
            .finally(() => {
                sendButton.disabled = false;
                sendButton.innerHTML = '<i class="bi bi-send"></i> Enviar';
                messageInput.focus();
            });
        }
        
        // Función para añadir mensaje al chat
        function addMessageToChat(message, isOwn, timestamp) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isOwn ? 'own' : 'other'}`;
            
            const timeStr = timestamp.toLocaleString('ca-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            messageDiv.innerHTML = `
                <div class="message-bubble">
                    ${message.replace(/\n/g, '<br>')}
                    <div class="message-info">
                        ${timeStr}
                    </div>
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
        }
        
        // Función para hacer scroll al final
        function scrollToBottom() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Función para obtener mensajes nuevos
        function checkNewMessages() {
            fetch(`<?php echo BASE_URL; ?>/index.php?controller=chat&action=getNewMessages&conversation_id=${conversationId}&last_message_id=${lastMessageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            addMessageToChat(
                                msg.message, 
                                msg.sender_id == <?php echo $_SESSION['user_id']; ?>, 
                                new Date(msg.created_at)
                            );
                            lastMessageId = Math.max(lastMessageId, msg.id);
                        });
                        scrollToBottom();
                    }
                })
                .catch(error => console.error('Error checking new messages:', error));
        }
        
        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
            
            // Focus en el input
            document.getElementById('messageInput').focus();
            
            // Verificar mensajes nuevos cada 3 segundos
            setInterval(checkNewMessages, 3000);
            
            // Enter para enviar
            document.getElementById('messageInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage(e);
                }
            });
        });
    </script>
</body>
</html>