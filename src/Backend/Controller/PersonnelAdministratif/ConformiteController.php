<?php
namespace App\Backend\Controller\PersonnelAdministratif;

use App\Backend\Controller\BaseController;
use App\Backend\Exception\DoublonException;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\Conformite\ServiceConformite; // Importer le service
use App\Backend\Service\Rapport\ServiceRapport; // Pour récupérer les détails des rapports
use App\Backend\Service\ConfigurationSysteme\ServiceConfigurationSysteme; // Pour lister les statuts de conformité
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\ValidationException;

class ConformiteController extends BaseController
{
    private ServiceConformite $conformiteService;
    private ServiceRapport $rapportService;
    private ServiceConfigurationSysteme $configService;

    public function __construct(
        ServiceAuthentication       $authService,
        ServicePermissions          $permissionService,
        FormValidator               $validator,
        ServiceConformite           $conformiteService,
        ServiceRapport              $rapportService,
        ServiceConfigurationSysteme $configService
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->conformiteService = $conformiteService;
        $this->rapportService = $rapportService;
        $this->configService = $configService;
    }

    /**
     * Affiche les listes de rapports à vérifier et de rapports traités par le personnel administratif.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_CONFORMITE_LISTER'); // Permission de lister les rapports de conformité

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_PERS_ADMIN') {
                throw new OperationImpossibleException("Accès refusé. Non personnel administratif.");
            }
            $numeroPersonnelAdministratif = $currentUser['numero_utilisateur'];

            $rapportsAVerifier = $this->conformiteService->recupererRapportsEnAttenteDeVerification();
            $rapportsTraites = $this->conformiteService->recupererRapportsTraitesParAgent($numeroPersonnelAdministratif);

            $data = [
                'page_title' => 'Vérification de Conformité des Rapports',
                'rapports_a_verifier' => $rapportsAVerifier,
                'rapports_traites' => $rapportsTraites,
                // Ajoutez des filtres ou pagination si nécessaire
            ];
            $this->render('PersonnelAdministratif/Conformite/index', $data); // Vue principale de Conformité
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement des rapports de conformité: " . $e->getMessage());
            $this->redirect('/dashboard/personnel-admin');
        }
    }

    /**
     * Affiche les détails d'un rapport pour la vérification de conformité et le formulaire de décision.
     * @param string $idRapport L'ID du rapport à vérifier.
     */
    public function showVerificationForm(string $idRapport): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER'); // Permission de vérifier un rapport

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_PERS_ADMIN') {
                throw new OperationImpossibleException("Accès refusé. Non personnel administratif.");
            }
            $numeroPersonnelAdministratif = $currentUser['numero_utilisateur'];

            $rapport = $this->rapportService->recupererInformationsRapportComplet($idRapport);
            if (!$rapport) {
                throw new ElementNonTrouveException("Rapport non trouvé.");
            }

            // Vérifier que le rapport est dans un état où il peut être vérifié par ce service
            if (!in_array($rapport['id_statut_rapport'], ['RAP_SOUMIS', 'RAP_NON_CONF'])) {
                throw new OperationImpossibleException("Le rapport n'est pas dans un état permettant la vérification de conformité.");
            }

            $statutsConformite = $this->configService->listerStatutsConformite(); // A ajouter au configService
            // Peut-être récupérer la dernière vérification de cet agent pour ce rapport si elle existe
            $lastVerification = $this->conformiteService->getVerificationByAgentAndRapport($numeroPersonnelAdministratif, $idRapport); // Nouvelle méthode au service si nécessaire

            $data = [
                'page_title' => 'Vérifier la Conformité du Rapport',
                'rapport' => $rapport,
                'statuts_conformite' => $statutsConformite,
                'last_verification' => $lastVerification,
                'form_action' => "/dashboard/personnel-admin/conformite/rapports/{$idRapport}/verify"
            ];
            $this->render('PersonnelAdministratif/Conformite/details_rapport_conformite', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur chargement formulaire vérification: ' . $e->getMessage());
            $this->redirect('/dashboard/personnel-admin/conformite');
        }
    }

    /**
     * Traite la soumission du verdict de conformité pour un rapport.
     * @param string $idRapport L'ID du rapport.
     */
    public function submitVerification(string $idRapport): void
    {
        $this->requirePermission('TRAIT_PERS_ADMIN_CONFORMITE_VERIFIER');

        if (!$this->isPostRequest()) {
            $this->redirect("/dashboard/personnel-admin/conformite/rapports/{$idRapport}/verify");
        }

        $currentUser = $this->getCurrentUser();
        if (!$currentUser || $currentUser['id_type_utilisateur'] !== 'TYPE_PERS_ADMIN') {
            $this->setFlashMessage('error', "Accès refusé.");
            $this->redirect("/dashboard/personnel-admin/conformite/rapports/{$idRapport}/verify");
        }
        $numeroPersonnelAdministratif = $currentUser['numero_utilisateur'];

        $idStatutConformite = $this->post('id_statut_conformite');
        $commentaireConformite = $this->post('commentaire_conformite');

        $rules = [
            'id_statut_conformite' => 'required|string|in:CONF_OK,CONF_NOK', // Assurez-vous que ces codes existent
            'commentaire_conformite' => 'nullable|string',
        ];
        // Si 'CONF_NOK', le commentaire doit être requis
        if ($idStatutConformite === 'CONF_NOK') {
            $rules['commentaire_conformite'] = 'required|string|min:10';
        }
        $validationData = [
            'id_statut_conformite' => $idStatutConformite,
            'commentaire_conformite' => $commentaireConformite,
        ];
        $this->validator->validate($validationData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/personnel-admin/conformite/rapports/{$idRapport}/verify");
        }

        try {
            $this->conformiteService->traiterVerificationConformite(
                $idRapport,
                $numeroPersonnelAdministratif,
                $idStatutConformite,
                $commentaireConformite
            );
            $this->setFlashMessage('success', 'Verdict de conformité enregistré avec succès.');
            $this->redirect('/dashboard/personnel-admin/conformite');
        } catch (DoublonException $e) { // Si l'agent vérifie deux fois
            $this->setFlashMessage('error', 'Vous avez déjà vérifié ce rapport.');
            $this->redirect("/dashboard/personnel-admin/conformite/rapports/{$idRapport}/verify");
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur lors de l\'enregistrement du verdict: ' . $e->getMessage());
            $this->redirect("/dashboard/personnel-admin/conformite/rapports/{$idRapport}/verify");
        }
    }

    // Les méthodes create(), update(), delete() génériques du template initial sont à supprimer.
    /*
    public function create(): void {}
    public function update($id): void {}
    public function delete($id): void {}
    */
}