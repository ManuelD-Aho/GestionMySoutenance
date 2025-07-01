<?php
/**
 * Composant Pied de page pour les pages d'authentification
 * Interface cohérente avec liens utiles et informations légales
 */

// Fonction d'échappement HTML sécurisée
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$currentYear = date('Y');
$showSupport = $showSupport ?? true;
$showLegal = $showLegal ?? true;
?>

<div class="auth-footer">
    <?php if ($showSupport): ?>
    <div class="footer-support">
        <div class="support-section">
            <h3 class="support-title">
                <span class="material-icons" aria-hidden="true">help_center</span>
                Besoin d'aide ?
            </h3>
            <div class="support-links">
                <a href="/support/faq" class="support-link">
                    <span class="material-icons" aria-hidden="true">quiz</span>
                    FAQ
                </a>
                <a href="/support/contact" class="support-link">
                    <span class="material-icons" aria-hidden="true">contact_support</span>
                    Contact
                </a>
                <a href="/support/guide" class="support-link">
                    <span class="material-icons" aria-hidden="true">menu_book</span>
                    Guide d'utilisation
                </a>
            </div>
        </div>
        
        <div class="emergency-contact">
            <p class="emergency-text">
                <span class="material-icons" aria-hidden="true">emergency</span>
                Support technique : 
                <a href="tel:+2252022334455" class="phone-link">+225 20 22 33 44 55</a>
            </p>
            <p class="hours-text">
                <span class="material-icons" aria-hidden="true">schedule</span>
                Lun-Ven: 8h-17h | Sam: 8h-12h
            </p>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="footer-divider"></div>
    
    <div class="footer-main">
        <div class="footer-info">
            <div class="institution-info">
                <p class="institution-name">Université Félix Houphouët-Boigny</p>
                <p class="institution-address">
                    22 BP 582 Abidjan 22, Côte d'Ivoire
                </p>
            </div>
            
            <div class="app-info">
                <p class="app-name">GestionMySoutenance</p>
                <p class="app-version">Version 2.0.0</p>
            </div>
        </div>
        
        <?php if ($showLegal): ?>
        <div class="footer-legal">
            <div class="legal-links">
                <a href="/legal/privacy" class="legal-link">Confidentialité</a>
                <span class="legal-separator">•</span>
                <a href="/legal/terms" class="legal-link">Conditions d'utilisation</a>
                <span class="legal-separator">•</span>
                <a href="/legal/cookies" class="legal-link">Cookies</a>
                <span class="legal-separator">•</span>
                <a href="/legal/accessibility" class="legal-link">Accessibilité</a>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="footer-copyright">
            <p class="copyright-text">
                &copy; <?= e($currentYear) ?> GestionMySoutenance. Tous droits réservés.
            </p>
            <p class="tech-info">
                Développé avec ❤️ pour l'excellence académique
            </p>
        </div>
    </div>
    
    <div class="footer-security">
        <div class="security-badges">
            <div class="security-badge" title="Connexion sécurisée SSL">
                <span class="material-icons" aria-hidden="true">lock</span>
                <span>SSL</span>
            </div>
            <div class="security-badge" title="Données protégées RGPD">
                <span class="material-icons" aria-hidden="true">verified_user</span>
                <span>RGPD</span>
            </div>
            <div class="security-badge" title="Authentification à deux facteurs">
                <span class="material-icons" aria-hidden="true">security</span>
                <span>2FA</span>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles pour le composant auth-footer */
.auth-footer {
    margin-top: var(--spacing-xl);
    padding: var(--spacing-xl) var(--spacing-lg) var(--spacing-lg);
    background: linear-gradient(135deg, 
        rgba(59, 130, 246, 0.02) 0%, 
        rgba(16, 185, 129, 0.02) 100%);
    border-top: 1px solid var(--border-light);
    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
}

.footer-support {
    text-align: center;
    margin-bottom: var(--spacing-lg);
}

.support-section {
    margin-bottom: var(--spacing-md);
}

.support-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-xs);
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-semibold);
    color: var(--primary-blue);
    margin-bottom: var(--spacing-md);
}

.support-links {
    display: flex;
    justify-content: center;
    gap: var(--spacing-lg);
    flex-wrap: wrap;
}

.support-link {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    color: var(--primary-blue);
    text-decoration: none;
    font-weight: var(--font-weight-medium);
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--border-radius-md);
    transition: all var(--transition-fast);
    background: rgba(59, 130, 246, 0.05);
    border: 1px solid transparent;
}

.support-link:hover {
    background: var(--primary-blue);
    color: var(--text-white);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary-blue);
}

.support-link .material-icons {
    font-size: 18px;
}

.emergency-contact {
    margin-top: var(--spacing-md);
    padding: var(--spacing-md);
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid var(--accent-yellow);
    border-radius: var(--border-radius-md);
    display: inline-block;
}

.emergency-text, .hours-text {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-xs);
    margin: 0;
    font-size: var(--font-size-sm);
    font-weight: var(--font-weight-medium);
    color: var(--accent-yellow-dark);
}

.hours-text {
    margin-top: var(--spacing-xs);
}

.phone-link {
    color: var(--accent-yellow-dark);
    text-decoration: none;
    font-weight: var(--font-weight-bold);
}

.phone-link:hover {
    text-decoration: underline;
}

.footer-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--border-light), transparent);
    margin: var(--spacing-lg) 0;
}

.footer-main {
    text-align: center;
}

.footer-info {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-lg);
    flex-wrap: wrap;
    gap: var(--spacing-md);
}

.institution-info, .app-info {
    flex: 1;
    min-width: 200px;
}

.institution-name, .app-name {
    font-weight: var(--font-weight-bold);
    color: var(--primary-blue);
    margin: 0 0 var(--spacing-xs) 0;
    font-size: var(--font-size-base);
}

.institution-address, .app-version {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
    margin: 0;
}

.footer-legal {
    margin-bottom: var(--spacing-lg);
}

.legal-links {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
}

.legal-link {
    color: var(--text-secondary);
    text-decoration: none;
    font-size: var(--font-size-sm);
    transition: color var(--transition-fast);
}

.legal-link:hover {
    color: var(--primary-blue);
    text-decoration: underline;
}

.legal-separator {
    color: var(--text-light);
    font-weight: var(--font-weight-bold);
}

.footer-copyright {
    margin-bottom: var(--spacing-md);
}

.copyright-text, .tech-info {
    font-size: var(--font-size-xs);
    color: var(--text-light);
    margin: 0;
}

.tech-info {
    margin-top: var(--spacing-xs);
    font-style: italic;
}

.footer-security {
    border-top: 1px solid var(--border-light);
    padding-top: var(--spacing-md);
}

.security-badges {
    display: flex;
    justify-content: center;
    gap: var(--spacing-md);
    flex-wrap: wrap;
}

.security-badge {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    padding: var(--spacing-xs) var(--spacing-sm);
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid var(--primary-green);
    border-radius: var(--border-radius-sm);
    color: var(--primary-green-dark);
    font-size: var(--font-size-xs);
    font-weight: var(--font-weight-bold);
    cursor: default;
}

.security-badge .material-icons {
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .auth-footer {
        padding: var(--spacing-lg) var(--spacing-md) var(--spacing-md);
    }
    
    .support-links {
        gap: var(--spacing-md);
    }
    
    .support-link {
        padding: var(--spacing-sm);
        font-size: var(--font-size-sm);
    }
    
    .footer-info {
        flex-direction: column;
        text-align: center;
        gap: var(--spacing-lg);
    }
    
    .legal-links {
        flex-direction: column;
        gap: var(--spacing-xs);
    }
    
    .legal-separator {
        display: none;
    }
}

@media (max-width: 480px) {
    .support-links {
        flex-direction: column;
        align-items: center;
    }
    
    .emergency-contact {
        max-width: 100%;
        text-align: center;
    }
    
    .emergency-text, .hours-text {
        flex-direction: column;
        gap: var(--spacing-xs);
    }
    
    .security-badges {
        gap: var(--spacing-xs);
    }
    
    .security-badge {
        padding: var(--spacing-xs);
    }
    
    .security-badge span:not(.material-icons) {
        display: none;
    }
}
</style>