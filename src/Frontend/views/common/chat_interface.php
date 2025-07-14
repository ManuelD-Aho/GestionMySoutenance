<?php
// /src/Frontend/views/common/chat_interface.php

if (!function_exists('e')) { function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); } }

// Ces données seraient passées par un CommunicationController
$conversations = $data['conversations'] ?? [];
$selected_conversation_id = $data['selected_conversation_id'] ?? null;
$current_conversation = $data['current_conversation'] ?? null;
$messages = $data['messages'] ?? [];
$participants = $data['participants'] ?? [];
$current_user_id = $_SESSION['user_id'] ?? null;
?>

<div class="card bg-base-100 shadow-xl h-[calc(100vh-120px)] flex flex-col">
    <div class="card-body p-0 flex flex-row h-full">
        <!-- Colonne des conversations -->
        <aside class="w-1/3 xl:w-1/4 border-r border-base-300 flex flex-col">
            <div class="p-4 border-b border-base-300">
                <h2 class="text-lg font-semibold">Conversations</h2>
                <!-- Potentiellement un champ de recherche ici -->
            </div>
            <ul class="menu p-2 overflow-y-auto flex-1">
                <?php if (empty($conversations)): ?>
                    <li class="p-4 text-center text-base-content/60">Aucune conversation.</li>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <li class="<?= ($conv['id'] == $selected_conversation_id) ? 'bordered' : '' ?>">
                            <a href="/chat?conv_id=<?= e($conv['id']) ?>">
                                <div class="flex flex-col">
                                    <span class="font-semibold"><?= e($conv['titre']) ?></span>
                                    <small class="opacity-70 truncate"><?= e($conv['derniers_messages']['contenu'] ?? '...') ?></small>
                                </div>
                                <?php if ($conv['unread']): ?>
                                    <span class="badge badge-primary badge-xs"></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </aside>

        <!-- Fenêtre de chat principale -->
        <main class="w-2/3 xl:w-3/4 flex flex-col h-full">
            <?php if ($current_conversation): ?>
                <!-- Header du chat -->
                <div class="p-4 border-b border-base-300">
                    <h3 class="font-bold"><?= e($current_conversation['titre']) ?></h3>
                    <p class="text-xs opacity-60">Participants: <?= e(implode(', ', array_column($participants, 'nom_complet'))) ?></p>
                </div>

                <!-- Zone des messages -->
                <div id="chat-messages" class="flex-1 p-4 overflow-y-auto space-y-4">
                    <?php foreach ($messages as $message): ?>
                        <div class="chat <?= ($message['expediteur_id'] == $current_user_id) ? 'chat-end' : 'chat-start' ?>">
                            <div class="chat-header text-xs opacity-50">
                                <?= e($message['expediteur_nom']) ?>
                                <time class="ml-1"><?= e(date('H:i', strtotime($message['timestamp']))) ?></time>
                            </div>
                            <div class="chat-bubble <?= ($message['expediteur_id'] == $current_user_id) ? 'chat-bubble-primary' : '' ?>">
                                <?= nl2br(e($message['contenu'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Zone de saisie -->
                <div class="p-4 border-t border-base-300">
                    <form id="sendMessageForm" class="flex gap-2">
                        <input type="hidden" name="conversation_id" value="<?= e($selected_conversation_id) ?>">
                        <textarea name="message_content" class="textarea textarea-bordered w-full" placeholder="Écrivez votre message..." rows="1"></textarea>
                        <button type="submit" class="btn btn-primary">
                            <span class="material-icons">send</span>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="flex-1 flex flex-col items-center justify-center text-base-content/60">
                    <span class="material-icons" style="font-size: 60px;">chat</span>
                    <p class="mt-4">Sélectionnez une conversation pour commencer.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>