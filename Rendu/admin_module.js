// admin_module.js

document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-item');
    const contentArea = document.getElementById('content-area');
    const mainTitle = document.getElementById('main-title');

    // --- Contenu HTML pour chaque section ---

    const sections = {
        dashboard: `
            <section class="overview-section">
                <div class="stats-grid">
                    <div class="dashboard-card stat-card">
                        <div class="stat-header"><h3 class="stat-label">Utilisateurs Actifs</h3><div class="stat-icon blue"><span class="material-icons">group</span></div></div>
                        <p class="stat-value">1,250</p>
                        <p class="stat-change positive"><span class="material-icons">arrow_upward</span>5%</p>
                    </div>
                    <div class="dashboard-card stat-card">
                         <div class="stat-header"><h3 class="stat-label">Rapports Soumis</h3><div class="stat-icon orange"><span class="material-icons">assignment</span></div></div>
                        <p class="stat-value">340</p>
                         <p class="stat-change positive"><span class="material-icons">arrow_upward</span>10</p>
                    </div>
                     <div class="dashboard-card stat-card">
                        <div class="stat-header"><h3 class="stat-label">PV Validés</h3><div class="stat-icon green"><span class="material-icons">check_circle</span></div></div>
                        <p class="stat-value">280</p>
                        <p class="stat-change positive"><span class="material-icons">arrow_upward</span>15</p>
                    </div>
                    <div class="dashboard-card stat-card">
                        <div class="stat-header"><h3 class="stat-label">Alertes Critiques</h3><div class="stat-icon red"><span class="material-icons">warning</span></div></div>
                        <p class="stat-value">3</p>
                         <p class="stat-change negative"><span class="material-icons">arrow_upward</span>1</p>
                    </div>
                </div>
            </section>
            <section class="admin-card system-alerts">
                <h3 class="card-title">Alertes Système Critiques</h3>
                 <div class="notification is-danger is-light"><span class="material-icons">error</span>Erreur BDD: Connexion impossible.</div>
                 <div class="notification is-warning is-light"><span class="material-icons">dns</span>Problème Serveur: CPU > 90%.</div>
                 <div class="notification is-warning is-light"><span class="material-icons">security</span>Accès suspect détecté (IP: 192.168.1.100).</div>
            </section>
             <section class="admin-card">
                <h3 class="card-title">Raccourcis</h3>
                <div class="buttons">
                    <button class="button is-primary is-light"><span class="material-icons">add</span> Nouvel Étudiant</button>
                    <button class="button is-link is-light"><span class="material-icons">settings</span> Configurer Année Acad.</button>
                    <button class="button is-info is-light"><span class="material-icons">receipt_long</span> Consulter Journaux</button>
                </div>
            </section>
        `,
        users: `
           <div class="admin-card">
                <div class="card-header">
                     <h3 class="card-title">Gestion des Utilisateurs</h3>
                     <button class="button is-primary"><span class="material-icons">add</span> Ajouter</button>
                </div>
                <div class="tabs is-boxed">
                  <ul>
                    <li class="is-active" data-tab="etudiants"><a><span class="material-icons">school</span><span>Étudiants</span></a></li>
                    <li data-tab="personnel"><a><span class="material-icons">support_agent</span><span>Personnel</span></a></li>
                    <li data-tab="enseignants"><a><span class="material-icons">history_edu</span><span>Enseignants</span></a></li>
                  </ul>
                </div>

                <div id="etudiants-tab" class="table-container">
                     <table class="table is-fullwidth is-striped is-hoverable">
                        <thead>
                            <tr><th>N° Carte</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Statut Compte</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>E12345</td><td>Dupont</td><td>Jean</td><td>jean.dupont@email.com</td><td><span class="tag is-success">Actif</span></td>
                                <td>
                                    <button class="button is-small is-info is-light"><span class="material-icons">visibility</span></button>
                                    <button class="button is-small is-warning is-light"><span class="material-icons">edit</span></button>
                                    <button class="button is-small is-danger is-light"><span class="material-icons">delete</span></button>
                                </td>
                            </tr>
                             <tr><td>E67890</td><td>Martin</td><td>Alice</td><td>alice.martin@email.com</td><td><span class="tag is-success">Actif</span></td>
                                 <td><button class="button is-small is-info is-light"><span class="material-icons">visibility</span></button><button class="button is-small is-warning is-light"><span class="material-icons">edit</span></button><button class="button is-small is-danger is-light"><span class="material-icons">delete</span></button></td>
                            </tr>
                             <tr><td>E11223</td><td>Bernard</td><td>Luc</td><td>luc.bernard@email.com</td><td><span class="tag is-danger">Inactif</span></td>
                                 <td><button class="button is-small is-info is-light"><span class="material-icons">visibility</span></button><button class="button is-small is-warning is-light"><span class="material-icons">edit</span></button><button class="button is-small is-danger is-light"><span class="material-icons">delete</span></button></td>
                             </tr>
                        </tbody>
                    </table>
                </div>

                 <div id="personnel-tab" class="table-container hidden-section">
                     <table class="table is-fullwidth is-striped is-hoverable">
                         <thead><tr><th>N° Pers.</th><th>Nom</th><th>Prénom</th><th>Email Pro</th><th>Rôle</th><th>Actions</th></tr></thead>
                         <tbody><tr><td>P001</td><td>Durand</td><td>Sophie</td><td>sophie.durand@univ.com</td><td>Scolarité</td><td>...</td></tr></tbody>
                     </table>
                </div>
                 <div id="enseignants-tab" class="table-container hidden-section">
                    <table class="table is-fullwidth is-striped is-hoverable">
                         <thead><tr><th>N° Ens.</th><th>Nom</th><th>Prénom</th><th>Email Pro</th><th>Grade</th><th>Actions</th></tr></thead>
                         <tbody><tr><td>ENS01</td><td>Leroy</td><td>Pierre</td><td>pierre.leroy@univ.com</td><td>Professeur</td><td>...</td></tr></tbody>
                    </table>
                </div>
           </div>
        `,
        permissions: `
            <div class="admin-card">
                 <h3 class="card-title">Gestion des Habilitations</h3>
                 <div class="columns">
                    <div class="column">
                        <h4 class="subtitle">Types Utilisateur (Rôles)</h4>
                        <ul class="referential-list">
                           <li>Étudiant <div class="buttons"><button class="button is-small"><span class="material-icons">edit</span></button><button class="button is-small is-danger"><span class="material-icons">delete</span></button></div></li>
                           <li>Enseignant <div class="buttons"><button class="button is-small"><span class="material-icons">edit</span></button><button class="button is-small is-danger"><span class="material-icons">delete</span></button></div></li>
                           <li>Administrateur <div class="buttons"><button class="button is-small"><span class="material-icons">edit</span></button><button class="button is-small is-danger"><span class="material-icons">delete</span></button></div></li>
                        </ul>
                         <button class="button is-primary is-light mt-2">Ajouter Rôle</button>
                    </div>
                     <div class="column">
                        <h4 class="subtitle">Groupes Utilisateur</h4>
                         <ul class="referential-list">
                           <li>Agents Conformité <div class="buttons"><button class="button is-small"><span class="material-icons">edit</span></button><button class="button is-small is-danger"><span class="material-icons">delete</span></button></div></li>
                           <li>Commission Validation <div class="buttons"><button class="button is-small"><span class="material-icons">edit</span></button><button class="button is-small is-danger"><span class="material-icons">delete</span></button></div></li>
                        </ul>
                         <button class="button is-primary is-light mt-2">Ajouter Groupe</button>
                    </div>
                 </div>
            </div>`,
        repositories: `
            <div class="admin-card">
                 <h3 class="card-title">Gestion des Référentiels (14)</h3>
                 <p class="subtitle is-6">Gérez les données de base du système.</p>
                 <ul class="referential-list">
                    <li>Spécialités <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Fonctions <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Grades <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Unités d'Enseignement (UE) <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Éléments Constitutifs d'UE (ECUE) <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Années Académiques <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Niveaux d'Étude <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Entreprises <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Niveaux d'Approbation <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Statuts Jury <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Actions (Audit) <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Traitements (Permissions) <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Messages (Modèles) <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                    <li>Notifications (Modèles) <div class="buttons"><button class="button is-small is-link"><span class="material-icons">settings_applications</span>Gérer</button></div></li>
                 </ul>
            </div>
        `,
        settings: `<div class="admin-card"><h3 class="card-title">Paramètres Généraux</h3><p>Configuration des dates limites, règles de validation, alertes, etc.</p></div>`,
        templates: `<div class="admin-card"><h3 class="card-title">Modèles de Documents & Notifications</h3><p>Gestion des modèles HTML/CSS pour PDF et emails.</p></div>`,
        academic: `<div class="admin-card"><h3 class="card-title">Gestion Académique</h3><p>Inscriptions, notes, stages, associations enseignants.</p></div>`,
        supervision: `<div class="admin-card"><h3 class="card-title">Supervision & Maintenance</h3><p>Workflows, PV, logs, import/export.</p></div>`,
        reporting: `<div class="admin-card"><h3 class="card-title">Reporting & Analytique</h3><p>Génération de rapports avancés.</p></div>`,
    };

    function loadSection(sectionId) {
        contentArea.innerHTML = sections[sectionId] || `<p>Section "${sectionId}" à construire.</p>`;
        mainTitle.textContent = document.querySelector(`[data-section="${sectionId}"] span:last-child`).textContent;

        // Gérer les tabs si la section 'users' est chargée
        if (sectionId === 'users') {
            setupUserTabs();
        }
    }

    function setupUserTabs() {
        const tabs = document.querySelectorAll('.tabs li');
        const tabContents = document.querySelectorAll('.table-container');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(item => item.classList.remove('is-active'));
                tab.classList.add('is-active');

                const targetTab = tab.dataset.tab;
                tabContents.forEach(content => {
                    if (content.id === `${targetTab}-tab`) {
                        content.classList.remove('hidden-section');
                    } else {
                        content.classList.add('hidden-section');
                    }
                });
            });
        });
    }


    navItems.forEach(item => {
        item.addEventListener('click', (event) => {
            event.preventDefault();
            navItems.forEach(link => link.classList.remove('active'));
            item.classList.add('active');
            const section = item.dataset.section;
            loadSection(section);
        });
    });

    // Charger le tableau de bord par défaut
    loadSection('dashboard');
});

// --- Fonctions globales (Sidebar, etc.) ---

function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('mobile-open');
}

// Close mobile sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.querySelector('.mobile-sidebar-toggle');

    if (window.innerWidth <= 768 &&
        sidebar && toggleBtn && // Check if elements exist
        !sidebar.contains(event.target) &&
        !toggleBtn.contains(event.target) &&
        sidebar.classList.contains('mobile-open')) {
        sidebar.classList.remove('mobile-open');
    }
});

// Responsive handling
function handleResize() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar && window.innerWidth > 768) {
        sidebar.classList.remove('mobile-open');
    }
}

window.addEventListener('resize', handleResize);
window.addEventListener('load', handleResize);