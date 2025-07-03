<?php
// src/Backend/Controller/DashboardController.php

namespace App\Backend\Controller;

use App\Backend\Service\Securite\ServiceSecuriteInterface;
use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Util\FormValidator;

class DashboardController extends BaseController
{
    public function __construct(
        ServiceSecuriteInterface $securiteService,
        ServiceSupervisionInterface $supervisionService,
        FormValidator $validator
    ) {
        parent::__construct($securiteService, $supervisionService, $validator);
    }

    /**
     * Point d'entrée après la connexion.
     * Redirige l'utilisateur vers son tableau de bord spécifique en fonction de son groupe.
     */
    public function index(): void
    {
        error_log("DEBUG DashboardController: Accès au tableau de bord. ID de Session actuel: " . session_id());
        error_log("DEBUG DashboardController: Données de session à l'accès du tableau de bord: " . json_encode($_SESSION));

        if (!$this->securiteService->estUtilisateurConnecte()) {
            error_log("DEBUG DashboardController: Utilisateur NON connecté, redirection vers la connexion. ID de Session: " . session_id());
            $this->redirect('/login');
            return;
        }

        $user = $this->securiteService->getUtilisateurConnecte();

        // --- CORRECTION MAJEURE ICI ---
        // La clé pour l'ID de l'utilisateur dans le tableau $user est 'numero_utilisateur', pas 'id_utilisateur'.
        // C'est pourquoi la vérification et les accès échouaient.
        if (!is_array($user) || !isset($user['numero_utilisateur'])) { // <-- LIGNE CORRIGÉE
            error_log("ERROR DashboardController: Données utilisateur en session incomplètes ou invalides. Clé 'numero_utilisateur' manquante.");
            $this->addFlashMessage('error', 'Vos informations de session sont invalides. Veuillez vous reconnecter.');
            $this->redirect('/login');
            return;
        }

        $dashboardUrl = null;

        // Assurez-vous que toutes les utilisations de l'ID utilisateur utilisent 'numero_utilisateur'
        $userIdForSupervision = $user['numero_utilisateur']; // <-- Utilisation cohérente

        switch ($user['id_groupe_utilisateur']) {
            case 'GRP_ADMIN_SYS':
                $dashboardUrl = '/admin/dashboard';
                break;
            case 'GRP_ETUDIANT':
                $dashboardUrl = '/etudiant/dashboard';
                break;
            case 'GRP_ENSEIGNANT':
            case 'GRP_COMMISSION':
                $dashboardUrl = '/commission/dashboard';
                break;
            case 'GRP_PERS_ADMIN':
            case 'GRP_RS':
            case 'GRP_AGENT_CONFORMITE':
                $dashboardUrl = '/personnel/dashboard';
                break;
            default:
                $this->addFlashMessage('error', 'Votre rôle ne vous donne pas accès à un tableau de bord spécifique.');
                $this->supervisionService->enregistrerAction(
                    $userIdForSupervision, // <-- Utilisation cohérente
                    'ACCES_DASHBOARD_REFUSE',
                    null,
                    null,
                    ['reason' => 'Groupe utilisateur non géré', 'group' => $user['id_groupe_utilisateur']]
                );
                $this->renderError(403, 'Accès non autorisé à un tableau de bord.');
                return;
        }

        if ($dashboardUrl) {
            $this->supervisionService->enregistrerAction(
                $userIdForSupervision, // <-- Utilisation cohérente
                'ACCES_DASHBOARD_REUSSI',
                null,
                $dashboardUrl,
                ['group' => $user['id_groupe_utilisateur']]
            );
            $this->redirect($dashboardUrl);
        }
    }
}