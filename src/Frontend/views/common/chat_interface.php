<?php
// src/Frontend/views/common/chat_interface.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Les données (conversations, messages, utilisateur courant) proviendraient du contrôleur de communication.
//
//

$current_user_id = $_SESSION['user_id'] ?? 1; // ID de l'utilisateur connecté, pour l'exemple

$conversations = $data['conversations'] ?? [
    ['id' => 1, 'titre' => 'Support Étudiants M2', 'type' => 'groupe', 'derniers_messages' => ['expediteur_nom' => 'Jean Dupont', 'contenu' => 'J\'ai une question sur le rapport.'], 'unread' => true],
    ['id' => 2, 'titre' => 'Conversation avec Pr. Martin', 'type' => 'direct', 'derniers_messages' => ['expediteur_nom' => 'Pr. Martin Sophie', 'contenu' => 'Rapport bien reçu.'], 'unread' => false],
    ['id' => 3, 'titre' => 'Commission PV Juin', 'type' => 'groupe', 'derniers_messages' => ['expediteur_nom' => 'Admin Principal', 'contenu' => 'Le PV est en attente d\'approbation.'], 'unread' => true],
];

// ID de la conversation actuellement sélectionnée (via GET param ou défaut)
$selected_conversation_id = $_GET['conv_id'] ?? ($conversations[0]['id'] ?? null);

$current_conversation = null;
$messages_conversation = [];
$participants_conversation = [];

// Simuler la récupération des messages et participants pour la conversation sélectionnée
if ($selected_conversation_id) {
    $current_conversation_data = array_filter($conversations, fn($conv) => $conv['id'] == $selected_conversation_id);
    $current_conversation = reset($current_conversation_data); // Prend le premier élément du filtre

    $messages_conversation = [
        ['id' => 1, 'expediteur_id' => 1, 'expediteur_nom' => 'Moi', 'contenu' => 'Bonjour l\'équipe, j\'ai une question concernant le statut des rapports.', 'timestamp' => '2025-06-30 09:00:00'],
        ['id' => 2, 'expediteur_id' => 5, 'expediteur_nom' => 'Jean Dupont', 'contenu' => 'Bonjour, je pense que mon rapport est passé au contrôle de conformité.', 'timestamp' => '2025-06-30 09:05:00'],
        ['id' => 3, 'expediteur_id' => 1, 'expediteur_nom' => 'Moi', 'contenu' => 'Oui, il est bien en cours d\'évaluation par la commission.', 'timestamp' => '2025-06-30 09:10:00'],
        ['id' => 4, 'expediteur_id' => 5, 'expediteur_nom' => 'Jean Dupont', 'contenu' => 'Merci pour l\'information !', 'timestamp' => '2025-06-30 09:12:00'],
    ];

    $participants_conversation = [
        ['id' => 1, 'nom_complet' => 'Moi'], // Utilisateur actuel
        ['id' => 5, 'nom_complet' => 'Jean Dupont'],
        ['id' => 6, 'nom_complet' => 'Marie Curie'],
    ];
}
?>

<div class="chat-container">
    <div class="chat-sidebar">
        <div class="sidebar-header">
            <h2>Conversations</h2>
            <a href="/commission/communication/create-conversation" class="btn-new-conversation" title="Nouvelle conversation">
                <span class="material-icons">add_box</span>
            </a>
        </div>
        <ul class="conversation-list">
            <?php if (!empty($conversations)): ?>
                <?php foreach ($conversations as $conv): ?>
                    <li class="conversation-item <?= ($conv['id'] == $selected_conversation_id) ? 'active' : ''; ?> <?= $conv['unread'] ? 'unread' : ''; ?>"
                        data-conversation-id="<?= e($conv['id']); ?>"
                        onclick="window.location.href='?conv_id=<?= e($conv['id']); ?>'">
                        <div class="conversation-info">
                            <h3><?= e($conv['titre']); ?></h3>
                            <p class="last-message">
                                <strong><?= e($conv['derniers_messages']['expediteur_nom']); ?>:</strong>
                                <?= e(mb_strimwidth($conv['derniers_messages']['contenu'], 0, 30, '...')); ?>
                            </p>
                        </div>
                        <?php if ($conv['unread']): ?><span class="unread-badge"></span><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="no-conversations">Aucune conversation.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="chat-main">
        <?php if ($current_conversation): ?>
            <div class="chat-header">
                <h3><?= e($current_conversation['titre']); ?></h3>
                <div class="chat-participants" title="Participants de la conversation">
                    <span class="material-icons">group</span>
                    <?php foreach ($participants_conversation as $participant): ?>
                        <span><?= e($participant['nom_complet']); ?></span><?= ($participant !== end($participants_conversation)) ? ',' : ''; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="chat-messages" id="chat-messages">
                <?php foreach ($messages_conversation as $message): ?>
                    <div class="message-bubble <?= ($message['expediteur_id'] == $current_user_id) ? 'sent' : 'received'; ?>">
                        <?php if ($message['expediteur_id'] != $current_user_id): ?>
                            <span class="sender-name"><?= e($message['expediteur_nom']); ?></span>
                        <?php endif; ?>
                        <p class="message-content"><?= e($message['contenu']); ?></p>
                        <span class="message-time"><?= e(date('H:i', strtotime($message['timestamp']))); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="chat-input-area">
                <form id="sendMessageForm" action="/api/chat/send-message" method="POST">
                    <input type="hidden" name="conversation_id" value="<?= e($selected_conversation_id); ?>">
                    <textarea id="message_content" name="message_content" placeholder="Écrivez votre message..." rows="1" required></textarea>
                    <button type="submit" class="btn btn-primary-blue send-button">
                        <span class="material-icons">send</span>
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="no-conversation-selected">
                <span class="material-icons">chat</span>
                <p>Sélectionnez une conversation pour commencer à discuter.</p>
                <a href="/commission/communication/create-conversation" class="btn btn-primary-blue mt-md">Créer une nouvelle conversation</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatMessagesContainer = document.getElementById('chat-messages');
        const sendMessageForm = document.getElementById('sendMessageForm');
        const messageContentInput = document.getElementById('message_content');

        // Fonction pour faire défiler vers le bas de la zone de messages
        function scrollToBottom() {
            if (chatMessagesContainer) {
                chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
            }
        }

        // Défilement au chargement
        scrollToBottom();

        // Envoi de message via AJAX
        if (sendMessageForm) {
            sendMessageForm.addEventListener('submit', function(event) {
                event.preventDefault();

                const messageContent = messageContentInput.value.trim();
                if (!messageContent) {
                    return; // Ne rien envoyer si le message est vide
                }

                const conversationId = this.querySelector('input[name="conversation_id"]').value;

                // Simuler l'envoi AJAX
                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        // 'X-CSRF-TOKEN': 'votre_token_csrf_ici'
                    },
                    body: JSON.stringify({
                        conversation_id: conversationId,
                        message_content: messageContent
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Ajouter le message à l'interface (simulé pour l'exemple)
                            const newMessage = document.createElement('div');
                            newMessage.className = 'message-bubble sent'; // Toujours 'sent' pour l'utilisateur actuel
                            newMessage.innerHTML = `
                        <p class="message-content">${e(messageContent)}</p>
                        <span class="message-time">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                    `;
                            chatMessagesContainer.appendChild(newMessage);
                            messageContentInput.value = ''; // Vider l'input
                            scrollToBottom(); // Défiler vers le nouveau message
                        } else {
                            alert('Erreur lors de l\'envoi du message : ' + (data.message || 'Erreur inconnue.'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur AJAX lors de l\'envoi du message:', error);
                        alert('Une erreur de communication est survenue lors de l\'envoi.');
                    });
            });

            // Ajuster la hauteur du textarea dynamiquement
            if (messageContentInput) {
                messageContentInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }
        }

        // Rechargement des messages (simulation d'une mise à jour en temps réel)
        // En production, cela se ferait via WebSockets ou un polling intelligent
        // setInterval(function() {
        //     if (selected_conversation_id) {
        //         fetch(`/api/chat/get-messages?conv_id=${selected_conversation_id}&last_message_id=${last_message_id}`)
        //             .then(response => response.json())
        //             .then(data => {
        //                 if (data.success && data.new_messages.length > 0) {
        //                     data.new_messages.forEach(msg => {
        //                         const newMessage = document.createElement('div');
        //                         newMessage.className = `message-bubble ${msg.expediteur_id == current_user_id ? 'sent' : 'received'}`;
        //                         newMessage.innerHTML = `
        //                             ${msg.expediteur_id != current_user_id ? `<span class="sender-name">${e(msg.expediteur_nom)}</span>` : ''}
        //                             <p class="message-content">${e(msg.contenu)}</p>
        //                             <span class="message-time">${new Date(msg.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
        //                         `;
        //                         chatMessagesContainer.appendChild(newMessage);
        //                         last_message_id = msg.id; // Mettre à jour le dernier ID
        //                     });
        //                     scrollToBottom();
        //                 }
        //             })
        //             .catch(error => console.error('Erreur de polling messages:', error));
        //     }
        // }, 5000); // Poll toutes les 5 secondes

    });
</script>

<style>
    /* Styles spécifiques pour chat_interface.php */
    /* Réutilisation des classes de root.css */

    body {
        background-color: var(--bg-secondary); /* Un fond légèrement grisé pour le dashboard */
    }

    /* Conteneur principal du chat */
    .chat-container {
        display: flex;
        height: calc(100vh - var(--header-height, 70px) - var(--spacing-xl)); /* Ajuster à la hauteur disponible après le header et avec marges */
        max-width: 1400px;
        margin: var(--spacing-xl) auto;
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-lg);
        overflow: hidden; /* Pour contenir les colonnes arrondies */
    }

    /* Sidebar des conversations */
    .chat-sidebar {
        width: 300px; /* Largeur fixe de la sidebar */
        flex-shrink: 0; /* Empêche la sidebar de se rétrécir */
        border-right: 1px solid var(--border-light);
        display: flex;
        flex-direction: column;
        background-color: var(--bg-secondary);
    }

    .sidebar-header {
        padding: var(--spacing-md);
        border-bottom: 1px solid var(--border-medium);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: var(--primary-white);
    }

    .sidebar-header h2 {
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        margin: 0;
    }

    .btn-new-conversation {
        background-color: var(--primary-green);
        color: var(--text-white);
        border: none;
        border-radius: var(--border-radius-md);
        padding: var(--spacing-xs) var(--spacing-sm);
        cursor: pointer;
        transition: background-color var(--transition-fast);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-base);
    }
    .btn-new-conversation:hover {
        background-color: var(--primary-green-dark);
    }
    .btn-new-conversation .material-icons {
        font-size: var(--font-size-xl);
    }


    .conversation-list {
        list-style: none;
        padding: 0;
        flex-grow: 1; /* Permet à la liste de prendre l'espace restant */
        overflow-y: auto; /* Défilement pour la liste des conversations */
    }

    .conversation-item {
        padding: var(--spacing-md);
        border-bottom: 1px solid var(--border-light);
        cursor: pointer;
        transition: background-color var(--transition-fast);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .conversation-item:hover {
        background-color: var(--primary-gray-light);
    }

    .conversation-item.active {
        background-color: var(--primary-blue-light);
        color: var(--text-white);
    }

    .conversation-item.active h3,
    .conversation-item.active p {
        color: var(--text-white);
    }

    .conversation-info h3 {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-medium);
    }

    .conversation-info p.last-message {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .conversation-info p.last-message strong {
        color: inherit; /* Hérite de la couleur du parent */
        font-weight: var(--font-weight-semibold);
    }

    .unread-badge {
        width: 10px;
        height: 10px;
        background-color: var(--accent-red);
        border-radius: var(--border-radius-full);
        flex-shrink: 0; /* Empêche le badge de se rétrécir */
        margin-left: var(--spacing-sm);
    }

    .no-conversations {
        padding: var(--spacing-md);
        text-align: center;
        color: var(--text-secondary);
        font-size: var(--font-size-sm);
    }

    /* Section principale du chat */
    .chat-main {
        flex-grow: 1; /* Prend l'espace restant */
        display: flex;
        flex-direction: column;
    }

    .chat-header {
        padding: var(--spacing-md);
        border-bottom: 1px solid var(--border-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: var(--primary-white);
    }

    .chat-header h3 {
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        margin: 0;
    }

    .chat-participants {
        display: flex;
        align-items: center;
        gap: var(--spacing-xs);
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }
    .chat-participants .material-icons {
        font-size: var(--font-size-base);
    }
    .chat-participants span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100px; /* Limiter la largeur des noms */
    }

    .chat-messages {
        flex-grow: 1; /* Permet à la zone de messages de s'étendre */
        overflow-y: auto; /* Défilement pour les messages */
        padding: var(--spacing-md);
        background-color: var(--bg-primary); /* Fond blanc pour la zone de messages */
        display: flex;
        flex-direction: column;
    }

    .message-bubble {
        max-width: 70%; /* Limiter la largeur des bulles de message */
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: var(--border-radius-lg);
        margin-bottom: var(--spacing-sm);
        line-height: var(--line-height-normal);
        box-shadow: var(--shadow-sm);
        position: relative;
    }

    .message-bubble.sent {
        background-color: var(--primary-blue-light);
        color: var(--text-white);
        align-self: flex-end; /* Aligner à droite pour les messages envoyés */
        border-bottom-right-radius: var(--border-radius-xs); /* Coin inférieur droit plus petit */
    }

    .message-bubble.received {
        background-color: var(--primary-gray-light);
        color: var(--text-primary);
        align-self: flex-start; /* Aligner à gauche pour les messages reçus */
        border-bottom-left-radius: var(--border-radius-xs); /* Coin inférieur gauche plus petit */
    }

    .message-bubble .sender-name {
        display: block;
        font-size: var(--font-size-xs);
        color: var(--text-secondary); /* Couleur pour le nom de l'expéditeur */
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-semibold);
    }

    .message-bubble.received .sender-name {
        color: var(--primary-blue-dark);
    }

    .message-bubble.sent .sender-name {
        color: var(--text-white);
    }

    .message-bubble p.message-content {
        font-size: var(--font-size-base);
        margin-bottom: var(--spacing-xs);
        word-wrap: break-word; /* Permettre le saut de ligne pour les longs mots */
    }

    .message-bubble .message-time {
        font-size: var(--font-size-xs);
        color: var(--text-light);
        display: block;
        text-align: right;
        margin-top: var(--spacing-xs);
    }

    .message-bubble.sent .message-time {
        color: rgba(255, 255, 255, 0.8);
    }

    .message-bubble.received .message-time {
        color: var(--text-light);
    }

    /* Zone de saisie de message */
    .chat-input-area {
        padding: var(--spacing-md);
        border-top: 1px solid var(--border-light);
        background-color: var(--primary-white);
    }

    .chat-input-area form {
        display: flex;
        gap: var(--spacing-sm);
        align-items: flex-end; /* Aligner le bouton avec le textearea */
    }

    .chat-input-area textarea {
        flex-grow: 1; /* Prend le maximum d'espace disponible */
        min-height: 40px;
        max-height: 150px; /* Limiter la croissance pour ne pas prendre tout l'écran */
        resize: none; /* Désactiver le redimensionnement manuel par l'utilisateur */
        padding: var(--spacing-sm) var(--spacing-md);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-lg);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-gray-light);
        transition: border-color var(--transition-fast);
    }

    .chat-input-area textarea:focus {
        border-color: var(--primary-blue);
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .send-button {
        flex-shrink: 0; /* Empêche le bouton de se rétrécir */
        padding: var(--spacing-sm) var(--spacing-md);
        border-radius: var(--border-radius-lg);
    }

    .send-button .material-icons {
        font-size: var(--font-size-xl);
    }

    /* Message si aucune conversation n'est sélectionnée */
    .no-conversation-selected {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: var(--text-secondary);
        font-size: var(--font-size-lg);
        text-align: center;
    }

    .no-conversation-selected .material-icons {
        font-size: var(--font-size-4xl);
        color: var(--primary-gray);
        margin-bottom: var(--spacing-md);
    }

    /* Réutilisations des boutons génériques */
    .btn { /* Base button style */ }
    .btn-primary-blue { /* Specific color */ }
    .btn-primary-blue:hover { /* Hover state */ }
    .mt-md { margin-top: var(--spacing-md); }

    /* Responsive adjustments */
    @media (max-width: var(--screen-md)) { /* Tablets */
        .chat-container {
            flex-direction: column;
            height: auto;
            min-height: 90vh;
            max-width: 95%;
        }
        .chat-sidebar {
            width: 100%;
            max-height: 250px; /* Limiter la hauteur de la sidebar sur mobile */
            border-right: none;
            border-bottom: 1px solid var(--border-light);
        }
        .conversation-list {
            display: flex;
            overflow-x: auto;
            white-space: nowrap;
            padding-bottom: var(--spacing-xs); /* Espace pour le scrollbar */
        }
        .conversation-item {
            flex: 0 0 auto; /* Empêche les items de se rétrécir */
            width: 180px; /* Largeur fixe pour chaque conversation en mode horizontal */
            border-bottom: none;
            border-right: 1px solid var(--border-light);
        }
        .chat-main {
            flex-grow: 1;
        }
    }

    @media (max-width: var(--screen-sm)) { /* Smartphones */
        .chat-sidebar {
            max-height: 200px;
        }
        .conversation-item {
            width: 150px;
        }
        .message-bubble {
            max-width: 85%;
        }
    }
</style>