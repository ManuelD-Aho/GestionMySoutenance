<?php
// src/Frontend/views/common/notifications_panel.php

// Fonction d'échappement HTML
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Données de notifications (proviennent du NotificationController)
//

$notifications = $data['notifications'] ?? [
    ['id' => 1, 'message' => 'Votre rapport "IA en Logistique" a été validé !', 'type' => 'success', 'read' => false, 'date' => '2025-06-30 15:30:00', 'link' => '/etudiant/rapport/suivi/1'],
    ['id' => 2, 'message' => 'Nouveau rapport "Gestion de Projet" à évaluer.', 'type' => 'info', 'read' => false, 'date' => '2025-06-30 14:00:00', 'link' => '/commission/rapports/details/123'],
    ['id' => 3, 'message' => 'Le PV de la session du 28/06 est en attente de votre approbation.', 'type' => 'warning', 'read' => false, 'date' => '2025-06-29 10:00:00', 'link' => '/commission/pv/valider/456'],
    ['id' => 4, 'message' => 'Votre compte sera bloqué si les pénalités ne sont pas réglées.', 'type' => 'error', 'read' => false, 'date' => '2025-06-28 08:00:00', 'link' => '/etudiant/penalites'],
    ['id' => 5, 'message' => 'Rappel : Mise à jour du système prévue demain soir.', 'type' => 'info', 'read' => true, 'date' => '2025-06-27 18:00:00', 'link' => '#'],
    ['id' => 6, 'message' => 'Votre mot de passe a été modifié avec succès.', 'type' => 'success', 'read' => true, 'date' => '2025-06-25 09:00:00', 'link' => '/profile'],
];

// Options de filtrage
$filter_status_options = ['ALL' => 'Tous les statuts', 'unread' => 'Non lues', 'read' => 'Lues'];
$filter_type_options = ['ALL' => 'Tous les types', 'info' => 'Information', 'success' => 'Succès', 'warning' => 'Avertissement', 'error' => 'Erreur'];

// Fonction utilitaire pour le temps (similaire à celle de main.js)
function timeAgo($dateString) {
    if (!$dateString) return 'N/A';
    $date = new DateTime($dateString);
    $now = new DateTime();
    $interval = $now->diff($date);

    if ($interval->y > 0) return 'il y a ' . $interval->y . ' an(s)';
    if ($interval->m > 0) return 'il y a ' . $interval->m . ' mois';
    if ($interval->d > 0) return 'il y a ' . $interval->d . ' jour(s)';
    if ($interval->h > 0) return 'il y a ' . $interval->h . ' heure(s)';
    if ($interval->i > 0) return 'il y a ' . $interval->i . ' minute(s)';
    return 'à l\'instant';
}
?>

<div class="common-dashboard-container">
    <h1 class="dashboard-title">Vos Notifications</h1>

    <section class="section-filters admin-card">
        <h2 class="section-title">Filtrer les Notifications</h2>
        <form id="notificationFilterForm" class="filter-form">
            <div class="form-group">
                <label for="filter_status">Statut :</label>
                <select id="filter_status" name="status">
                    <?php foreach ($filter_status_options as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['status'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_type">Type :</label>
                <select id="filter_type" name="type">
                    <?php foreach ($filter_type_options as $code => $libelle): ?>
                        <option value="<?= e($code); ?>" <?= (($_GET['type'] ?? 'ALL') === $code) ? 'selected' : ''; ?>>
                            <?= e($libelle); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="filter_keyword">Recherche :</label>
                <input type="text" id="filter_keyword" name="keyword" value="<?= e($_GET['keyword'] ?? ''); ?>" placeholder="Rechercher...">
            </div>
            <button type="submit" class="btn btn-primary-blue">
                <span class="material-icons">filter_list</span> Appliquer
            </button>
            <button type="button" class="btn btn-secondary-gray" onclick="window.location.href='/dashboard/notifications'">
                <span class="material-icons">clear</span> Réinitialiser
            </button>
        </form>
    </section>

    <section class="section-list admin-card mt-xl">
        <div class="section-header">
            <h2 class="section-title">Liste des Notifications</h2>
            <div class="action-buttons">
                <button id="markAllReadBtn" class="btn btn-primary-green">
                    <span class="material-icons">mark_as_unread</span> Tout marquer comme lu
                </button>
                <button id="archiveReadBtn" class="btn btn-secondary-gray ml-md">
                    <span class="material-icons">archive</span> Archiver les lues
                </button>
            </div>
        </div>

        <?php if (!empty($notifications)): ?>
            <div class="notifications-list-full" id="notificationsListFull">
                <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item-full <?= $notif['read'] ? 'read' : 'unread'; ?> notification-<?= e($notif['type']); ?>" data-id="<?= e($notif['id']); ?>" data-link="<?= e($notif['link'] ?? ''); ?>">
                        <div class="notification-icon-full" style="background-color: var(--<?= e($notif['type']); ?>-color-light); color: var(--<?= e($notif['type']); ?>-color-dark);">
                            <span class="material-icons">
                                <?php
                                if ($notif['type'] === 'info') echo 'info';
                                elseif ($notif['type'] === 'success') echo 'check_circle';
                                elseif ($notif['type'] === 'warning') echo 'warning_amber';
                                elseif ($notif['type'] === 'error') echo 'error';
                                else echo 'notifications';
                                ?>
                            </span>
                        </div>
                        <div class="notification-content-full">
                            <p class="notification-message-full"><?= e($notif['message']); ?></p>
                            <span class="notification-date-full"><?= timeAgo($notif['date']); ?></span>
                        </div>
                        <div class="notification-actions-full">
                            <?php if (!$notif['read']): ?>
                                <button class="btn-action mark-read-btn" title="Marquer comme lu">
                                    <span class="material-icons">mark_email_read</span>
                                </button>
                            <?php else: ?>
                                <button class="btn-action mark-unread-btn" title="Marquer comme non lu">
                                    <span class="material-icons">mark_email_unread</span>
                                </button>
                            <?php endif; ?>
                            <button class="btn-action delete-notification-btn" title="Supprimer">
                                <span class="material-icons">delete</span>
                            </button>
                            <?php if (!empty($notif['link'])): ?>
                                <a href="<?= e($notif['link']); ?>" class="btn-action view-notification-link" title="Voir les détails">
                                    <span class="material-icons">visibility</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="pagination-controls mt-lg text-center">
                <button class="btn btn-secondary-gray" disabled>Précédent</button>
                <span class="current-page">Page 1 de X</span>
                <button class="btn btn-secondary-gray">Suivant</button>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">Aucune notification à afficher pour le moment.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationsListFull = document.getElementById('notificationsListFull');
        const notificationFilterForm = document.getElementById('notificationFilterForm');
        const markAllReadBtn = document.getElementById('markAllReadBtn');
        const archiveReadBtn = document.getElementById('archiveReadBtn');

        // --- Fonctions de gestion des notifications (similaires à main.js mais adaptées) ---

        // Récupérer la couleur de fond de l'icône de notification depuis root.css
        function getNotificationIconBgColor(type) {
            const rootStyles = getComputedStyle(document.documentElement);
            switch (type) {
                case 'success': return rootStyles.getPropertyValue('--primary-green-light');
                case 'warning': return rootStyles.getPropertyValue('--accent-yellow-light');
                case 'error': return rootStyles.getPropertyValue('--accent-red-light');
                case 'info': return rootStyles.getPropertyValue('--primary-blue-light');
                default: return rootStyles.getPropertyValue('--primary-gray-light');
            }
        }

        // Récupérer l'icône Material Icons en fonction du type
        function getNotificationIcon(type) {
            switch (type) {
                case 'success': return 'check_circle';
                case 'warning': return 'warning_amber';
                case 'error': return 'error';
                case 'info': return 'info';
                default: return 'notifications';
            }
        }

        // Marquer une notification comme lue/non lue
        function markNotificationStatus(notificationElement, readStatus) {
            const notificationId = notificationElement.dataset.id;
            const actionUrl = `/api/notifications/${readStatus ? 'mark-read' : 'mark-unread'}`;

            fetch(actionUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: notificationId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (readStatus) {
                            notificationElement.classList.remove('unread');
                            notificationElement.classList.add('read');
                            // Remplacer le bouton "Marquer comme lu" par "Marquer comme non lu"
                            notificationElement.querySelector('.mark-read-btn').outerHTML = `
                        <button class="btn-action mark-unread-btn" title="Marquer comme non lu">
                            <span class="material-icons">mark_email_unread</span>
                        </button>
                    `;
                        } else {
                            notificationElement.classList.remove('read');
                            notificationElement.classList.add('unread');
                            // Remplacer le bouton "Marquer comme non lu" par "Marquer comme lu"
                            notificationElement.querySelector('.mark-unread-btn').outerHTML = `
                        <button class="btn-action mark-read-btn" title="Marquer comme lu">
                            <span class="material-icons">mark_email_read</span>
                        </button>
                    `;
                        }
                        // Optionnel: Dispatcher un événement pour le header pour mettre à jour le badge
                        // document.dispatchEvent(new CustomEvent('notificationStatusChanged'));
                        if (window.DashboardHeader && typeof window.DashboardHeader.updateNotificationBadge === 'function') {
                            window.DashboardHeader.updateNotificationBadge();
                        }
                    } else {
                        alert('Erreur: ' + (data.message || 'Impossible de changer le statut.'));
                    }
                })
                .catch(error => {
                    console.error('Erreur AJAX:', error);
                    alert('Erreur de communication.');
                });
        }

        // Supprimer une notification
        function deleteNotification(notificationElement) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
                return;
            }

            const notificationId = notificationElement.dataset.id;
            fetch(`/api/notifications/delete/${notificationId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notificationElement.remove(); // Supprimer de l'UI
                        // Optionnel: Mettre à jour le badge du header
                        if (window.DashboardHeader && typeof window.DashboardHeader.updateNotificationBadge === 'function') {
                            window.DashboardHeader.updateNotificationBadge();
                        }
                    } else {
                        alert('Erreur: ' + (data.message || 'Impossible de supprimer la notification.'));
                    }
                })
                .catch(error => {
                    console.error('Erreur AJAX:', error);
                    alert('Erreur de communication.');
                });
        }

        // --- Listeners ---

        // Gestion du formulaire de filtre
        if (notificationFilterForm) {
            notificationFilterForm.addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(notificationFilterForm);
                const queryParams = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value && value !== 'ALL') {
                        queryParams.append(key, value);
                    }
                }
                window.location.href = `/dashboard/notifications?${queryParams.toString()}`;
            });
        }

        // Délégation d'événements pour les boutons d'action sur les notifications
        if (notificationsListFull) {
            notificationsListFull.addEventListener('click', function(event) {
                const target = event.target;
                const notificationItem = target.closest('.notification-item-full');
                if (!notificationItem) return;

                if (target.closest('.mark-read-btn')) {
                    markNotificationStatus(notificationItem, true);
                } else if (target.closest('.mark-unread-btn')) {
                    markNotificationStatus(notificationItem, false);
                } else if (target.closest('.delete-notification-btn')) {
                    deleteNotification(notificationItem);
                } else if (target.closest('.view-notification-link')) {
                    // Le lien A gère déjà la redirection
                } else {
                    // Comportement par défaut si on clique sur la notification elle-même
                    const link = notificationItem.dataset.link;
                    if (link && link !== '#') {
                        window.location.href = link;
                    } else if (notificationItem.classList.contains('unread')) {
                        // Si pas de lien et non lue, marquer comme lue juste au clic
                        markNotificationStatus(notificationItem, true);
                    }
                }
            });
        }

        // Bouton "Tout marquer comme lu"
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function() {
                if (confirm('Marquer toutes les notifications comme lues ?')) {
                    fetch('/api/notifications/mark-all-read', { method: 'POST' })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.querySelectorAll('.notification-item-full.unread').forEach(item => {
                                    item.classList.remove('unread');
                                    item.classList.add('read');
                                    // Mettre à jour les boutons d'action individuels
                                    item.querySelector('.mark-read-btn')?.outerHTML = `<button class="btn-action mark-unread-btn" title="Marquer comme non lu"><span class="material-icons">mark_email_unread</span></button>`;
                                });
                                alert('Toutes les notifications ont été marquées comme lues.');
                                if (window.DashboardHeader && typeof window.DashboardHeader.updateNotificationBadge === 'function') {
                                    window.DashboardHeader.updateNotificationBadge();
                                }
                            } else {
                                alert('Erreur : ' + (data.message || 'Impossible de marquer toutes les notifications comme lues.'));
                            }
                        })
                        .catch(error => console.error('Erreur AJAX:', error));
                }
            });
        }

        // Bouton "Archiver les lues"
        if (archiveReadBtn) {
            archiveReadBtn.addEventListener('click', function() {
                if (confirm('Archiver toutes les notifications lues ? Elles ne seront plus visibles ici.')) {
                    fetch('/api/notifications/archive-read', { method: 'POST' })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.querySelectorAll('.notification-item-full.read').forEach(item => item.remove());
                                alert('Notifications lues archivées avec succès.');
                            } else {
                                alert('Erreur : ' + (data.message || 'Impossible d\'archiver les notifications lues.'));
                            }
                        })
                        .catch(error => console.error('Erreur AJAX:', error));
                }
            });
        }

        // Gestion de l'affichage des messages flash
        const flashMessage = "<?= $_SESSION['flash_message'] ?? ''; ?>";
        if (flashMessage) {
            console.log("Message Flash:", flashMessage);
            <?php unset($_SESSION['flash_message']); ?>
        }
    });
</script>

<style>
    /* Styles spécifiques pour notifications_panel.php */
    /* Réutilisation des classes de root.css et admin_module.css */

    /* Conteneur et titres principaux - réutilisés */
    .common-dashboard-container { /* Renommé pour correspondre à dashboard.php */
        padding: var(--spacing-lg);
        background-color: var(--bg-primary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        max-width: 1000px;
        margin: var(--spacing-xl) auto;
    }

    .dashboard-title { /* Réutilisé de dashboard.php */
        font-size: var(--font-size-2xl);
        color: var(--text-primary);
        margin-bottom: var(--spacing-sm);
        text-align: center;
        font-weight: var(--font-weight-semibold);
        padding-bottom: var(--spacing-xs);
        border-bottom: 1px solid var(--border-light);
    }

    .admin-card { /* Réutilisé des modules d'administration */
        background-color: var(--bg-secondary);
        border-radius: var(--border-radius-md);
        box-shadow: var(--shadow-sm);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-xl);
    }

    .section-header { /* Réutilisé des listes d'administration */
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: var(--spacing-lg);
        border-bottom: 1px solid var(--border-medium);
        padding-bottom: var(--spacing-sm);
    }

    .section-title { /* Réutilisé des listes d'administration */
        font-size: var(--font-size-xl);
        color: var(--text-primary);
        font-weight: var(--font-weight-medium);
        margin: 0;
    }

    .action-buttons { /* Boutons d'action en haut de liste */
        display: flex;
        gap: var(--spacing-sm);
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    /* Filtres - réutilisés et adaptés */
    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-lg);
        align-items: flex-end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
        margin-bottom: var(--spacing-xs);
        font-weight: var(--font-weight-medium);
    }

    .form-group input[type="text"],
    .form-group select {
        padding: var(--spacing-sm);
        border: 1px solid var(--border-medium);
        border-radius: var(--border-radius-sm);
        font-size: var(--font-size-base);
        color: var(--text-primary);
        background-color: var(--primary-white);
        transition: border-color var(--transition-fast);
        width: 100%;
    }

    /* Boutons */
    .btn {
        padding: var(--spacing-sm) var(--spacing-md);
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-semibold);
        border: none;
        border-radius: var(--border-radius-sm);
        cursor: pointer;
        transition: background-color var(--transition-fast), box-shadow var(--transition-fast);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: var(--spacing-xs);
        text-decoration: none;
    }

    .btn-primary-blue {
        color: var(--text-white);
        background-color: var(--primary-blue);
    }

    .btn-primary-blue:hover {
        background-color: var(--primary-blue-dark);
        box-shadow: var(--shadow-sm);
    }

    .btn-primary-green {
        color: var(--text-white);
        background-color: var(--primary-green);
    }
    .btn-primary-green:hover {
        background-color: var(--primary-green-dark);
        box-shadow: var(--shadow-sm);
    }

    .btn-secondary-gray {
        color: var(--text-primary);
        background-color: var(--primary-gray-light);
        border: 1px solid var(--border-medium);
    }

    .btn-secondary-gray:hover {
        background-color: var(--border-medium);
        box-shadow: var(--shadow-sm);
    }

    .ml-md { margin-left: var(--spacing-md); }

    /* Liste complète des notifications */
    .notifications-list-full {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm); /* Espacement entre les notifications */
    }

    .notification-item-full {
        display: flex;
        align-items: flex-start; /* Aligne l'icône en haut */
        gap: var(--spacing-md);
        padding: var(--spacing-md);
        border: 1px solid var(--border-light);
        border-radius: var(--border-radius-md);
        background-color: var(--primary-white);
        box-shadow: var(--shadow-sm);
        transition: all var(--transition-fast);
    }

    .notification-item-full:hover {
        background-color: var(--hover-bg);
    }

    .notification-item-full.unread {
        background-color: var(--primary-blue-light); /* Couleur de fond pour non lues */
        border-left: 5px solid var(--primary-blue);
        box-shadow: var(--shadow-md);
    }

    .notification-item-full.read {
        opacity: 0.8; /* Légèrement estompé si lue */
    }


    .notification-icon-full {
        flex-shrink: 0; /* Empêche l'icône de se compresser */
        width: 40px;
        height: 40px;
        border-radius: var(--border-radius-full);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: var(--font-size-xl);
        color: var(--text-white); /* Couleur de l'icône elle-même */
    }

    /* Couleurs de fond des icônes de notification (réutilisées de root.css) */
    /* Les couleurs spécifiques sont définies par JS via style="..." */


    .notification-content-full {
        flex-grow: 1;
    }

    .notification-message-full {
        font-size: var(--font-size-base);
        color: var(--text-primary);
        margin-bottom: var(--spacing-xs);
        line-height: var(--line-height-normal);
    }

    .notification-item-full.unread .notification-message-full {
        color: var(--text-white);
        font-weight: var(--font-weight-medium);
    }

    .notification-item-full.unread .notification-date-full {
        color: rgba(255, 255, 255, 0.8);
    }


    .notification-date-full {
        font-size: var(--font-size-sm);
        color: var(--text-secondary);
    }

    .notification-actions-full {
        display: flex;
        gap: var(--spacing-xs);
        flex-shrink: 0;
        align-items: center;
    }

    .btn-action { /* Réutilisé des tableaux d'administration */
        background: none;
        border: none;
        cursor: pointer;
        padding: var(--spacing-xs);
        border-radius: var(--border-radius-sm);
        transition: background-color var(--transition-fast);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        font-size: var(--font-size-xl);
        text-decoration: none;
    }

    .btn-action:hover {
        background-color: var(--primary-gray-light);
    }

    .btn-action.mark-read-btn, .btn-action.mark-unread-btn {
        color: var(--primary-green);
    }
    .btn-action.mark-read-btn:hover, .btn-action.mark-unread-btn:hover {
        background-color: rgba(16, 185, 129, 0.1);
    }

    .btn-action.delete-notification-btn {
        color: var(--accent-red);
    }
    .btn-action.delete-notification-btn:hover {
        background-color: rgba(239, 68, 68, 0.1);
    }

    .btn-action.view-notification-link {
        color: var(--primary-blue);
    }
    .btn-action.view-notification-link:hover {
        background-color: rgba(59, 130, 246, 0.1);
    }


    /* Pagination */
    .pagination-controls {
        margin-top: var(--spacing-lg);
        display: flex;
        justify-content: center;
        align-items: center;
        gap: var(--spacing-md);
    }
    .pagination-controls button {
        margin: 0; /* Réinitialiser la marge du bouton générique */
    }
    .pagination-controls .current-page {
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        font-size: var(--font-size-base);
    }

    /* Utilitaires */
    .text-center { text-align: center; }
    .text-muted { color: var(--text-light); }
    .mt-xl { margin-top: var(--spacing-xl); }
    .mt-lg { margin-top: var(--spacing-lg); }
    .mt-md { margin-top: var(--spacing-md); }