/**
 * Fichier: Public/assets/js/app.js
 * Description: Script principal pour les pages authentifiées de l'application.
 * Gère le changement de thème, les animations et l'interactivité générale.
 */
document.addEventListener('DOMContentLoaded', () => {

    // --- 1. GESTION DU THÈME (DAISYUI) ---
    const themeSwitcherButtons = document.querySelectorAll('[data-set-theme]');
    const htmlTag = document.documentElement;
    const savedTheme = localStorage.getItem('theme') || 'light';

    // Appliquer le thème sauvegardé au chargement
    htmlTag.setAttribute('data-theme', savedTheme);

    // Ajouter les écouteurs d'événements pour changer de thème
    themeSwitcherButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const theme = button.getAttribute('data-set-theme');
            htmlTag.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme); // Sauvegarder le choix
        });
    });


    // --- 2. ANIMATIONS D'ENTRÉE (GSAP) ---
    // Animation douce pour le contenu principal de la page
    gsap.from('main > *', {
        duration: 0.6,
        opacity: 0,
        y: 20,
        stagger: 0.1,
        ease: 'power3.out'
    });

    // Animation pour la barre de navigation
    gsap.from('.navbar', {
        duration: 0.5,
        opacity: 0,
        y: -50,
        ease: 'power2.out'
    });


    // --- 3. INTERACTIVITÉ DU MENU LATÉRAL (DRAWER) ---
    const drawer = document.getElementById('my-drawer-2');
    const drawerLinks = document.querySelectorAll('.drawer-side .menu a');

    // Fermer le menu latéral sur mobile après avoir cliqué sur un lien
    if (drawer && window.innerWidth < 1024) { // lg breakpoint de Tailwind
        drawerLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (drawer.checked) {
                    drawer.checked = false;
                }
            });
        });
    }

    // --- 4. GESTION DES NOTIFICATIONS/ALERTES ---
    // Permet de fermer les alertes en cliquant dessus
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.cursor = 'pointer';
        alert.addEventListener('click', (e) => {
            gsap.to(e.currentTarget, {
                duration: 0.3,
                opacity: 0,
                height: 0,
                padding: 0,
                margin: 0,
                onComplete: () => e.currentTarget.remove()
            });
        });
    });

});