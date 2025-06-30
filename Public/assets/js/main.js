// ===== MAIN.JS - Script JavaScript principal pour GestionMySoutenance (hors authentification) =====

// Variables globales et éléments du DOM du header (réaffirmées pour clarté, déjà dans le même scope)
let searchTimeout;
let fullscreenBtn;
let notificationDropdown;
let userDropdown;
let searchInput;
let searchSuggestions;

// --- Sidebar/Menu related variables ---
const sidebar = document.getElementById('sidebar');
const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
const body = document.body;
let isSidebarCollapsed = localStorage.getItem('sidebar_collapsed') === 'true'; // État persistant

document.addEventListener('DOMContentLoaded', function() {
    // --- Initialisation des éléments du DOM (réaffirmées pour clarté) ---
    const menuToggle = document.getElementById('menuToggle');
    searchInput = document.getElementById('searchInput');
    const searchClear = document.getElementById('searchClear');
    searchSuggestions = document.getElementById('searchSuggestions');
    const notificationBtn = document.getElementById('notificationBtn');
    notificationDropdown = document.getElementById('notificationDropdown'); // Assignation
    const userProfile = document.getElementById('userProfile');
    userDropdown = document.getElementById('userDropdown'); // Assignation
    fullscreenBtn = document.getElementById('fullscreenBtn'); // Assignation
    const markAllReadBtnHeader = document.querySelector('#notificationDropdown .mark-all-read'); // Bouton dans le dropdown du header
    const notificationListHeader = document.getElementById('notificationList'); // Liste dans le dropdown du header

    // --- Listeners pour le Header ---

    // Toggle menu latéral (communication avec le sidebar - assumant qu'il existe)
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            document.dispatchEvent(new CustomEvent('toggleSidebar'));
            this.style.transform = 'rotate(90deg)';
            setTimeout(() => { this.style.transform = 'rotate(0deg)'; }, 200);
        });
    }

    // Gestion de la recherche
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const value = this.value.trim();
            if (value) {
                searchClear.style.display = 'block';
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    fetchSearchSuggestions(value);
                }, 300);
            } else {
                searchClear.style.display = 'none';
                hideSearchSuggestions();
            }
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); performGlobalSearch(this.value.trim()); }
            else if (e.key === 'Escape') { hideSearchSuggestions(); this.blur(); }
        });

        searchInput.addEventListener('focus', function() {
            if (this.value.trim()) { fetchSearchSuggestions(this.value.trim()); }
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            this.style.display = 'none';
            hideSearchSuggestions();
        });
    }

    // Toggle notifications
    if (notificationBtn && notificationDropdown) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown(notificationDropdown);
            if (userDropdown && userDropdown.classList.contains('show')) { userDropdown.classList.remove('show'); }
            if (notificationDropdown.classList.contains('show')) { loadNotificationsHeader(); } // Charger notifications au besoin
        });
    }

    // Toggle menu utilisateur
    if (userProfile && userDropdown) {
        userProfile.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleDropdown(userDropdown);
            if (notificationDropdown && notificationDropdown.classList.contains('show')) { notificationDropdown.classList.remove('show'); }
        });
    }

    // Bouton plein écran
    if (fullscreenBtn) {
        fullscreenBtn.addEventListener('click', function() { toggleFullscreen(); });
        document.addEventListener('fullscreenchange', updateFullscreenIcon);
    }

    // Fermer les dropdowns en cliquant ailleurs
    document.addEventListener('click', function(e) {
        if (notificationDropdown && !notificationDropdown.contains(e.target) && !notificationBtn.contains(e.target)) { notificationDropdown.classList.remove('show'); }
        if (userDropdown && !userDropdown.contains(e.target) && !userProfile.contains(e.target)) { userDropdown.classList.remove('show'); }
        if (searchSuggestions && !searchSuggestions.contains(e.target) && !searchInput.contains(e.target)) { hideSearchSuggestions(); }
    });

    // Marquer toutes les notifications comme lues (Header Dropdown)
    if (markAllReadBtnHeader) {
        markAllReadBtnHeader.addEventListener('click', function() { markAllNotificationsAsReadHeader(); });
    }

    // Gestion des clics sur les notifications individuelles (Header Dropdown)
    if (notificationListHeader) {
        notificationListHeader.addEventListener('click', function(e) {
            const item = e.target.closest('.notification-item');
            if (item && item.classList.contains('unread')) { markNotificationAsReadHeader(item); }
            const notificationLink = item?.dataset.link;
            if (notificationLink && notificationLink !== '#') {
                setTimeout(() => window.location.href = notificationLink, 100);
            }
        });
    }


    // --- Fonctions utilitaires du Header ---

    function toggleDropdown(dropdownElement) { /* ... (Code de la fonction toggleDropdown) ... */ }
    function fetchSearchSuggestions(query) { /* ... (Code de la fonction fetchSearchSuggestions) ... */ }
    function hideSearchSuggestions() { /* ... (Code de la fonction hideSearchSuggestions) ... */ }
    function performGlobalSearch(queryOrLink) { /* ... (Code de la fonction performGlobalSearch) ... */ }
    function toggleFullscreen() { /* ... (Code de la fonction toggleFullscreen) ... */ }
    function updateFullscreenIcon() { /* ... (Code de la fonction updateFullscreenIcon) ... */ }
    function loadNotificationsHeader() { /* ... (Code de la fonction loadNotificationsHeader) ... */ }
    function getNotificationIcon(type) { /* ... (Code de la fonction getNotificationIcon) ... */ }
    function getNotificationIconBgColor(type) { /* ... (Code de la fonction getNotificationIconBgColor) ... */ }
    function markNotificationAsReadHeader(notificationElement) { /* ... (Code de la fonction markNotificationAsReadHeader) ... */ }
    function markAllNotificationsAsReadHeader() { /* ... (Code de la fonction markAllNotificationsAsReadHeader) ... */ }
    function updateNotificationBadge() { /* ... (Code de la fonction updateNotificationBadge) ... */ }
    function timeAgo(dateString) { /* ... (Code de la fonction timeAgo) ... */ }


    // --- Fonctions globales exposées ---
    window.DashboardHeader = { /* ... (Code de l'objet DashboardHeader) ... */ };


    // --- Sidebar/Menu Functions ---
    function applySidebarState() {
        if (isSidebarCollapsed) {
            body.classList.add('sidebar-collapsed');
            sidebar.classList.add('collapsed');
        } else {
            body.classList.remove('sidebar-collapsed');
            sidebar.classList.remove('collapsed');
        }
        // Pour les écrans plus petits, si le sidebar est "visible" (pas transformX(-100%)), ajuster le body
        if (window.innerWidth <= 768) {
            if (sidebar.classList.contains('visible')) {
                body.style.overflow = 'hidden'; // Empêche le scroll du contenu derrière le menu
            } else {
                body.style.overflow = '';
            }
        }
    }

    function toggleSidebar() {
        if (window.innerWidth <= 768) { // Sur mobile
            sidebar.classList.toggle('visible'); // Gérer la visibilité
            body.classList.toggle('no-scroll'); // Empêcher le défilement du body
        } else { // Sur desktop
            isSidebarCollapsed = !isSidebarCollapsed;
            localStorage.setItem('sidebar_collapsed', isSidebarCollapsed); // Persister l'état
        }
        applySidebarState();
    }

    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', toggleSidebar);
    }

    // Écouter l'événement 'toggleSidebar' du header
    document.addEventListener('toggleSidebar', toggleSidebar);

    // Gérer la fermeture du sidebar sur mobile si l'on clique en dehors
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && sidebar.classList.contains('visible') && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            toggleSidebar(); // Ferme le sidebar
        }
    });

    // Initialiser l'état du sidebar au chargement de la page
    applySidebarState();

    // Gestion des sous-menus
    document.querySelectorAll('.menu-item.has-submenu > .menu-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const parentItem = this.closest('.menu-item');
            if (window.innerWidth <= 768 || sidebar.classList.contains('collapsed')) {
                e.preventDefault(); // Empêche la navigation directe si le sous-menu doit s'ouvrir
                parentItem.classList.toggle('open'); // Bascule la classe 'open' pour afficher/masquer
            }
            // Fermer les autres sous-menus au même niveau
            parentItem.parentNode.querySelectorAll('.menu-item.has-submenu.open').forEach(otherItem => {
                if (otherItem !== parentItem) {
                    otherItem.classList.remove('open');
                }
            });
        });
    });

    // Marquer l'élément de menu actif (lors du chargement ou de la navigation)
    function highlightActiveMenuItem() {
        const currentPath = window.location.pathname.split('?')[0]; // Ignorer les paramètres GET
        document.querySelectorAll('.menu-link').forEach(link => {
            let linkHref = link.getAttribute('href');
            if (linkHref && linkHref.startsWith('/') && linkHref !== '#') {
                // Pour les liens exacts
                if (linkHref === currentPath) {
                    link.classList.add('active');
                    let parent = link.closest('.menu-item.has-submenu');
                    while (parent) {
                        parent.classList.add('active-parent');
                        parent.classList.add('open'); // Ouvre les sous-menus parents
                        parent = parent.closest('.menu-item.has-submenu'); // Remonter la hiérarchie
                    }
                } else {
                    link.classList.remove('active');
                }
            }
        });
    }
    highlightActiveMenuItem(); // Appeler au chargement

    // --- Autres initialisations globales ---
    initThemeToggle(); // Fonction de gestion du thème (déjà définie)
    // initRealTimeNotifications(); // Fonction de notifications en temps réel (déjà définie)

    console.log('Main JavaScript chargé et initialisé');
});