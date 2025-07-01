document.addEventListener('DOMContentLoaded', () => {
    // Animation douce pour le contenu principal de la page
    gsap.from('main > *', { duration: 0.6, opacity: 0, y: 20, stagger: 0.1, ease: 'power3.out' });

    // Animation pour la barre de navigation
    gsap.from('.navbar', { duration: 0.5, opacity: 0, y: -50, ease: 'power2.out' });

    // Fermer le menu latéral sur mobile après avoir cliqué sur un lien
    const drawer = document.getElementById('my-drawer-2');
    if (drawer && window.innerWidth < 1024) {
        document.querySelectorAll('.drawer-side .menu a').forEach(link => {
            link.addEventListener('click', () => {
                if (drawer.checked) drawer.checked = false;
            });
        });
    }

    // Permet de fermer les alertes flash en cliquant dessus
    document.querySelectorAll('.alert').forEach(alert => {
        alert.style.cursor = 'pointer';
        alert.addEventListener('click', (e) => {
            gsap.to(e.currentTarget, {
                duration: 0.3, opacity: 0, height: 0, padding: 0, margin: 0,
                onComplete: () => e.currentTarget.remove()
            });
        });
    });
});