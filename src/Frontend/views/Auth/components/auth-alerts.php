<?php
/**
 * Composant Alertes pour les pages d'authentification
 * Système d'alertes moderne avec animations et accessibilité
 */

// Fonction d'échappement HTML sécurisée
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Affiche une alerte selon le type et le message
 * @param string $type Type d'alerte : success, error, warning, info
 * @param string $message Message à afficher
 * @param bool $dismissible Si l'alerte peut être fermée
 * @param array $actions Actions supplémentaires (boutons)
 */
function renderAlert($type, $message, $dismissible = true, $actions = []) {
    $iconMap = [
        'success' => 'check_circle',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info'
    ];
    
    $icon = $iconMap[$type] ?? 'info';
    $alertId = 'alert-' . uniqid();
    ?>
    
    <div class="auth-alert alert-<?= e($type) ?>" 
         id="<?= e($alertId) ?>" 
         role="alert" 
         aria-live="polite"
         data-alert-type="<?= e($type) ?>">
        
        <div class="alert-icon" aria-hidden="true">
            <span class="material-icons"><?= e($icon) ?></span>
        </div>
        
        <div class="alert-content">
            <div class="alert-message">
                <?= e($message) ?>
            </div>
            
            <?php if (!empty($actions)): ?>
            <div class="alert-actions">
                <?php foreach ($actions as $action): ?>
                <button type="button" 
                        class="alert-action-btn btn-<?= e($action['type'] ?? 'secondary') ?>"
                        <?php if (isset($action['onclick'])): ?>onclick="<?= e($action['onclick']) ?>"<?php endif; ?>
                        <?php if (isset($action['data'])): ?>
                            <?php foreach ($action['data'] as $key => $value): ?>
                                data-<?= e($key) ?>="<?= e($value) ?>"
                            <?php endforeach; ?>
                        <?php endif; ?>>
                    <?php if (isset($action['icon'])): ?>
                    <span class="material-icons" aria-hidden="true"><?= e($action['icon']) ?></span>
                    <?php endif; ?>
                    <?= e($action['text']) ?>
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($dismissible): ?>
        <button type="button" 
                class="alert-close" 
                onclick="closeAlert('<?= e($alertId) ?>')"
                aria-label="Fermer l'alerte"
                title="Fermer">
            <span class="material-icons" aria-hidden="true">close</span>
        </button>
        <?php endif; ?>
        
        <div class="alert-progress" aria-hidden="true"></div>
    </div>
    
    <?php
}

// Afficher les alertes depuis la session si elles existent
if (isset($_SESSION['flash_messages'])) {
    foreach ($_SESSION['flash_messages'] as $flashMessage) {
        renderAlert(
            $flashMessage['type'], 
            $flashMessage['message'], 
            $flashMessage['dismissible'] ?? true,
            $flashMessage['actions'] ?? []
        );
    }
    // Nettoyer les messages flash après affichage
    unset($_SESSION['flash_messages']);
}

// Conteneur pour les alertes dynamiques
?>
<div id="dynamic-alerts-container" class="alerts-container" aria-live="polite"></div>

<style>
/* Styles pour le composant auth-alerts */
.alerts-container {
    position: fixed;
    top: var(--spacing-lg);
    right: var(--spacing-lg);
    z-index: 9999;
    max-width: 400px;
    width: 100%;
    pointer-events: none;
}

.auth-alert {
    display: flex;
    align-items: flex-start;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-md);
    border-radius: var(--border-radius-lg);
    border: 1px solid;
    background: var(--bg-primary);
    box-shadow: var(--shadow-lg);
    position: relative;
    overflow: hidden;
    pointer-events: auto;
    transform: translateX(100%);
    opacity: 0;
    animation: slideInAlert 0.4s ease-out forwards;
    transition: all var(--transition-normal);
}

.auth-alert.closing {
    animation: slideOutAlert 0.3s ease-in forwards;
}

@keyframes slideInAlert {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutAlert {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

/* Types d'alertes */
.alert-success {
    border-color: var(--primary-green);
    background: linear-gradient(135deg, 
        rgba(16, 185, 129, 0.05) 0%, 
        rgba(16, 185, 129, 0.02) 100%);
}

.alert-success .alert-icon {
    color: var(--primary-green);
}

.alert-error {
    border-color: var(--accent-red);
    background: linear-gradient(135deg, 
        rgba(239, 68, 68, 0.05) 0%, 
        rgba(239, 68, 68, 0.02) 100%);
}

.alert-error .alert-icon {
    color: var(--accent-red);
}

.alert-warning {
    border-color: var(--accent-yellow);
    background: linear-gradient(135deg, 
        rgba(245, 158, 11, 0.05) 0%, 
        rgba(245, 158, 11, 0.02) 100%);
}

.alert-warning .alert-icon {
    color: var(--accent-yellow-dark);
}

.alert-info {
    border-color: var(--primary-blue);
    background: linear-gradient(135deg, 
        rgba(59, 130, 246, 0.05) 0%, 
        rgba(59, 130, 246, 0.02) 100%);
}

.alert-info .alert-icon {
    color: var(--primary-blue);
}

/* Composants de l'alerte */
.alert-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 2px;
}

.alert-icon .material-icons {
    font-size: 24px;
    animation: iconPulse 2s ease-in-out infinite;
}

@keyframes iconPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.alert-content {
    flex: 1;
    min-width: 0;
}

.alert-message {
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--text-primary);
    line-height: 1.5;
    margin-bottom: var(--spacing-sm);
}

.alert-actions {
    display: flex;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
}

.alert-action-btn {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    padding: var(--spacing-xs) var(--spacing-sm);
    border: none;
    border-radius: var(--border-radius-sm);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-semibold);
    cursor: pointer;
    transition: all var(--transition-fast);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.alert-action-btn .material-icons {
    font-size: 16px;
}

.btn-primary {
    background: var(--primary-blue);
    color: var(--text-white);
}

.btn-primary:hover {
    background: var(--primary-blue-dark);
    transform: translateY(-1px);
}

.btn-secondary {
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border-medium);
}

.btn-secondary:hover {
    background: var(--text-secondary);
    color: var(--text-white);
}

.alert-close {
    position: absolute;
    top: var(--spacing-sm);
    right: var(--spacing-sm);
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    padding: var(--spacing-xs);
    border-radius: var(--border-radius-sm);
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    justify-content: center;
}

.alert-close:hover {
    background: rgba(0, 0, 0, 0.1);
    color: var(--text-primary);
    transform: scale(1.1);
}

.alert-close .material-icons {
    font-size: 18px;
}

/* Barre de progression auto-dismiss */
.alert-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: linear-gradient(90deg, 
        rgba(59, 130, 246, 0.3) 0%, 
        rgba(59, 130, 246, 0.7) 50%, 
        rgba(59, 130, 246, 0.3) 100%);
    width: 0%;
    transition: width linear;
    border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);
}

.alert-success .alert-progress {
    background: linear-gradient(90deg, 
        rgba(16, 185, 129, 0.3) 0%, 
        rgba(16, 185, 129, 0.7) 50%, 
        rgba(16, 185, 129, 0.3) 100%);
}

.alert-error .alert-progress {
    background: linear-gradient(90deg, 
        rgba(239, 68, 68, 0.3) 0%, 
        rgba(239, 68, 68, 0.7) 50%, 
        rgba(239, 68, 68, 0.3) 100%);
}

.alert-warning .alert-progress {
    background: linear-gradient(90deg, 
        rgba(245, 158, 11, 0.3) 0%, 
        rgba(245, 158, 11, 0.7) 50%, 
        rgba(245, 158, 11, 0.3) 100%);
}

/* Responsive */
@media (max-width: 768px) {
    .alerts-container {
        top: var(--spacing-md);
        right: var(--spacing-md);
        left: var(--spacing-md);
        max-width: none;
    }
    
    .auth-alert {
        margin-bottom: var(--spacing-sm);
        padding: var(--spacing-sm);
    }
    
    .alert-message {
        font-size: var(--font-size-xs);
    }
    
    .alert-actions {
        flex-direction: column;
    }
    
    .alert-action-btn {
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .alerts-container {
        top: var(--spacing-sm);
        right: var(--spacing-sm);
        left: var(--spacing-sm);
    }
    
    .alert-icon .material-icons {
        font-size: 20px;
    }
    
    .alert-close .material-icons {
        font-size: 16px;
    }
}

/* Animations spéciales pour certains types */
.alert-error .alert-icon {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.alert-success .alert-icon {
    animation: bounce 0.6s ease-out;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
}
</style>

<script>
/**
 * Ferme une alerte avec animation
 */
function closeAlert(alertId) {
    const alert = document.getElementById(alertId);
    if (alert) {
        alert.classList.add('closing');
        setTimeout(() => {
            alert.remove();
        }, 300);
    }
}

/**
 * Crée et affiche une alerte dynamique
 */
function showAlert(type, message, options = {}) {
    const {
        dismissible = true,
        duration = 5000,
        actions = [],
        position = 'top-right'
    } = options;
    
    const alertId = 'alert-' + Date.now();
    const container = document.getElementById('dynamic-alerts-container');
    
    if (!container) return;
    
    const iconMap = {
        'success': 'check_circle',
        'error': 'error',
        'warning': 'warning',
        'info': 'info'
    };
    
    const icon = iconMap[type] || 'info';
    
    const alertHTML = `
        <div class="auth-alert alert-${type}" 
             id="${alertId}" 
             role="alert" 
             aria-live="polite"
             data-alert-type="${type}">
            
            <div class="alert-icon" aria-hidden="true">
                <span class="material-icons">${icon}</span>
            </div>
            
            <div class="alert-content">
                <div class="alert-message">${message}</div>
                ${actions.length > 0 ? `
                <div class="alert-actions">
                    ${actions.map(action => `
                        <button type="button" 
                                class="alert-action-btn btn-${action.type || 'secondary'}"
                                onclick="${action.onclick || ''}"
                                ${action.data ? Object.entries(action.data).map(([k,v]) => `data-${k}="${v}"`).join(' ') : ''}>
                            ${action.icon ? `<span class="material-icons" aria-hidden="true">${action.icon}</span>` : ''}
                            ${action.text}
                        </button>
                    `).join('')}
                </div>
                ` : ''}
            </div>
            
            ${dismissible ? `
            <button type="button" 
                    class="alert-close" 
                    onclick="closeAlert('${alertId}')"
                    aria-label="Fermer l'alerte"
                    title="Fermer">
                <span class="material-icons" aria-hidden="true">close</span>
            </button>
            ` : ''}
            
            <div class="alert-progress" aria-hidden="true"></div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', alertHTML);
    
    // Auto-dismiss avec barre de progression
    if (duration > 0) {
        const alertElement = document.getElementById(alertId);
        const progressBar = alertElement.querySelector('.alert-progress');
        
        // Animer la barre de progression
        setTimeout(() => {
            progressBar.style.transition = `width ${duration}ms linear`;
            progressBar.style.width = '100%';
        }, 100);
        
        // Fermer automatiquement
        setTimeout(() => {
            closeAlert(alertId);
        }, duration);
    }
}

// Export global pour utilisation externe
window.AuthAlerts = {
    show: showAlert,
    close: closeAlert
};
</script>