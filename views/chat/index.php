<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xat - DriveShare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .chat-sidebar {
            height: calc(100vh - 100px);
            overflow-y: auto;
            border-right: 1px solid #dee2e6;
        }
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #f8f9fa;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .conversation-item:hover {
            background-color: #f8f9fa;
        }
        .conversation-item.active {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
        }
        .unread-badge {
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.75rem;
            margin-left: auto;
        }
        .last-message {
            color: #6c757d;
            font-size: 0.9rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .chat-empty {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 60vh;
            flex-direction: column;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../templates/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Sidebar de conversaciones -->
            <div class="col-md-4 col-lg-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-chat-dots"></i> Les meves converses
                        </h5>
                    </div>
                    <div class="chat-sidebar">
                        <?php if (empty($conversations)): ?>
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-chat-text display-4"></i>
                                <p class="mt-2">No tens converses encara</p>
                                <a href="../ver-coches.php" class="btn btn-primary btn-sm">
                                    <i class="bi bi-car-front"></i> Veure vehicles
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversations as $conversation): ?>
                                <div class="conversation-item" onclick="openConversation(<?php echo $conversation['id']; ?>)">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-1">
                                                    <?php echo htmlspecialchars($conversation['vehicle_name']); ?>
                                                </h6>
                                                <?php if ($conversation['unread_count'] > 0): ?>
                                                    <span class="unread-badge"><?php echo $conversation['unread_count']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mb-1 text-muted small">
                                                <?php echo htmlspecialchars($conversation['brand'] . ' ' . $conversation['model']); ?>
                                            </p>
                                            <p class="mb-1 text-muted small">
                                                Amb: <?php 
                                                    if ($_SESSION['user_id'] == $conversation['owner_id']) {
                                                        echo htmlspecialchars($conversation['renter_name']);
                                                    } else {
                                                        echo htmlspecialchars($conversation['owner_name']);
                                                    }
                                                ?>
                                            </p>
                                            <?php if ($conversation['last_message']): ?>
                                                <p class="last-message mb-1">
                                                    <?php echo htmlspecialchars(substr($conversation['last_message'], 0, 50)) . (strlen($conversation['last_message']) > 50 ? '...' : ''); ?>
                                                </p>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y H:i', strtotime($conversation['last_message_time'])); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Área principal -->
            <div class="col-md-8 col-lg-9">
                <div class="chat-empty">
                    <i class="bi bi-chat-square-text display-1"></i>
                    <h4 class="mt-3">Selecciona una conversa</h4>
                    <p>Tria una conversa de l'esquerra per començar a xatejar</p>
                    <a href="../ver-coches.php" class="btn btn-primary">
                        <i class="bi bi-car-front"></i> Buscar vehicles
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openConversation(conversationId) {
            window.location.href = 'ChatController.php?action=conversation&id=' + conversationId;
        }

        // Actualizar contador de mensajes no leídos cada 30 segundos
        setInterval(function() {
            fetch('ChatController.php?action=unread-count')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.count > 0) {
                        // Actualizar badge en navbar si existe
                        const badge = document.querySelector('.navbar .badge');
                        if (badge) {
                            badge.textContent = data.count;
                        }
                    }
                })
                .catch(error => console.error('Error updating unread count:', error));
        }, 30000);
    </script>
</body>
</html>