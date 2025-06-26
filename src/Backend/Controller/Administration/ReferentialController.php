<?php
namespace App\Backend\Controller\Administration;

use App\Backend\Controller\BaseController;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Model\BaseModel; // Pour les opérations génériques sur les référentiels
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\DoublonException;
use App\Backend\Exception\ValidationException;

// Modèles de référentiel à gérer (assurez-vous qu'ils sont importés et définis dans le Container)
use App\Backend\Model\DecisionPassageRef;
use App\Backend\Model\DecisionValidationPvRef;
use App\Backend\Model\DecisionVoteRef;
use App\Backend\Model\StatutConformiteRef;
use App\Backend\Model\StatutJury;
use App\Backend\Model\StatutPaiementRef;
use App\Backend\Model\StatutPvRef;
use App\Backend\Model\StatutRapportRef;
use App\Backend\Model\StatutReclamationRef;
use App\Backend\Model\TypeDocumentRef;
use App\Backend\Model\StatutPenaliteRef; // Nouveau référentiel

class ReferentialController extends BaseController
{
    // Définir la liste des référentiels gérés par ce contrôleur générique
    private const REFERENTIAL_MAP = [
        'decision_passage' => DecisionPassageRef::class,
        'decision_validation_pv' => DecisionValidationPvRef::class,
        'decision_vote' => DecisionVoteRef::class,
        'statut_conformite' => StatutConformiteRef::class,
        'statut_jury' => StatutJury::class,
        'statut_paiement' => StatutPaiementRef::class,
        'statut_pv' => StatutPvRef::class,
        'statut_rapport' => StatutRapportRef::class,
        'statut_reclamation' => StatutReclamationRef::class,
        'type_document' => TypeDocumentRef::class,
        'statut_penalite' => StatutPenaliteRef::class, // Nouveau
        // Ajoutez d'autres référentiels ici
    ];

    public function __construct(
        ServiceAuthentication $authService,
        ServicePermissions    $permissionService,
        FormValidator         $validator
    ) {
        parent::__construct($authService, $permissionService, $validator);
    }

    /**
     * Affiche la liste des catégories de référentiels gérés.
     */
    public function index(): void
    {
        $this->requirePermission('TRAIT_ADMIN_REFERENTIELS_ACCEDER'); // Permission générale

        $referentielsList = [];
        foreach (self::REFERENTIAL_MAP as $code => $class) {
            // Vous pouvez obtenir le libellé plus lisible ici si le modèle a une propriété 'libelle'
            $referentielsList[] = [
                'code' => $code,
                'libelle' => ucfirst(str_replace('_', ' ', $code)), // Simple conversion pour l'affichage
                'url' => "/dashboard/admin/referentiels/{$code}/list"
            ];
        }
        $data = [
            'page_title' => 'Gestion des Référentiels',
            'referentiels_list' => $referentielsList
        ];
        $this->render('Administration/Referentiels/liste_referentiels', $data);
    }

    /**
     * Affiche la liste des éléments d'un référentiel spécifique.
     * @param string $referentielCode Le code du référentiel (ex: 'statut_conformite').
     */
    public function listItems(string $referentielCode): void
    {
        $this->requirePermission('TRAIT_ADMIN_REFERENTIELS_LISTER'); // Permission plus spécifique ou par référentiel

        if (!isset(self::REFERENTIAL_MAP[$referentielCode])) {
            $this->setFlashMessage('error', "Référentiel '{$referentielCode}' non trouvé ou non géré.");
            $this->redirect('/dashboard/admin/referentiels');
            return;
        }

        try {
            $modelClass = self::REFERENTIAL_MAP[$referentielCode];
            // Instancier le modèle via le conteneur pour obtenir PDO
            $model = $this->getModelInstance($modelClass); // Nouvelle méthode utilitaire

            $items = $model->trouverTout(); // Récupère tous les items du référentiel

            $data = [
                'page_title' => 'Gestion du Référentiel: ' . ucfirst(str_replace('_', ' ', $referentielCode)),
                'referentiel_code' => $referentielCode,
                'items' => $items,
                'model_primary_key_name' => is_array($model->getClePrimaire()) ? $model->getClePrimaire()[0] : $model->getClePrimaire() // Pour la vue
            ];
            $this->render('Administration/Referentiels/crud_referentiel_generique', $data);
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement du référentiel: " . $e->getMessage());
            $this->redirect('/dashboard/admin/referentiels');
        }
    }

    /**
     * Affiche le formulaire de création/modification d'un élément de référentiel ou la traite.
     * @param string $referentielCode Le code du référentiel.
     * @param string|null $id L'ID de l'élément à modifier, ou null pour la création.
     */
    public function handleItemForm(string $referentielCode, string $id = null): void
    {
        $isEdit = ($id !== null);
        $permission = $isEdit ? 'TRAIT_ADMIN_REFERENTIELS_MODIFIER' : 'TRAIT_ADMIN_REFERENTIELS_CREER';
        $this->requirePermission($permission);

        if (!isset(self::REFERENTIAL_MAP[$referentielCode])) {
            $this->setFlashMessage('error', "Référentiel '{$referentielCode}' non trouvé.");
            $this->redirect('/dashboard/admin/referentiels');
            return;
        }

        $modelClass = self::REFERENTIAL_MAP[$referentielCode];
        $model = $this->getModelInstance($modelClass);
        $modelPrimaryKeyName = is_array($model->getClePrimaire()) ? $model->getClePrimaire()[0] : $model->getClePrimaire();

        if ($this->isPostRequest()) {
            $this->handleSubmitItem($referentielCode, $model, $modelPrimaryKeyName, $id);
        } else {
            $item = null;
            if ($isEdit) {
                $item = $model->trouverParIdentifiant($id); // Utilise la méthode de BaseModel
                if (!$item) {
                    throw new ElementNonTrouveException("Élément de référentiel non trouvé.");
                }
            }
            $data = [
                'page_title' => ($isEdit ? 'Modifier ' : 'Ajouter ') . ucfirst(str_replace('_', ' ', $referentielCode)),
                'referentiel_code' => $referentielCode,
                'item' => $item,
                'model_primary_key_name' => $modelPrimaryKeyName,
                'form_action' => $isEdit ? "/dashboard/admin/referentiels/{$referentielCode}/edit/{$id}" : "/dashboard/admin/referentiels/{$referentielCode}/create"
            ];
            $this->render('Administration/Referentiels/form_referentiel_generique', $data); // Créer cette vue
        }
    }

    /**
     * Traite la soumission du formulaire d'un élément de référentiel.
     * @param string $referentielCode Le code du référentiel.
     * @param BaseModel $model L'instance du modèle du référentiel.
     * @param string $modelPrimaryKeyName Le nom de la clé primaire du modèle.
     * @param string|null $id L'ID de l'élément (pour modification).
     */
    private function handleSubmitItem(string $referentielCode, BaseModel $model, string $modelPrimaryKeyName, ?string $id): void
    {
        $isEdit = ($id !== null);
        $itemData = [
            $modelPrimaryKeyName => $isEdit ? $id : $this->getRequestData($modelPrimaryKeyName),
            'libelle_' . $referentielCode => $this->getRequestData('libelle_' . $referentielCode),
            // Ajoutez d'autres champs spécifiques aux référentiels si nécessaire
            // Par exemple, pour statut_rapport_ref, il y a 'etape_workflow'
            'etape_workflow' => $this->getRequestData('etape_workflow', null),
            'abreviation_grade' => $this->getRequestData('abreviation_grade', null), // Pour Grade
            'requis_ou_non' => (bool)$this->getRequestData('requis_ou_non', false), // Pour TypeDocumentRef
            'numero_enseignant_specialite' => $this->getRequestData('numero_enseignant_specialite', null), // Pour Specialite
        ];
        // Nettoyage des nulls pour ne pas écraser si champ non pertinent
        $itemData = array_filter($itemData, fn($value) => $value !== null, ARRAY_FILTER_USE_BOTH);

        $rules = [
            $modelPrimaryKeyName => 'required|string|max:50',
            'libelle_' . $referentielCode => 'required|string|max:100',
            // Ajoutez des règles spécifiques pour les autres champs si nécessaire
            'etape_workflow' => 'nullable|integer',
            'abreviation_grade' => 'nullable|string|max:10',
            'requis_ou_non' => 'nullable|boolean',
            'numero_enseignant_specialite' => 'nullable|string|max:50',
        ];
        $this->validator->validate($itemData, $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/admin/referentiels/{$referentielCode}/" . ($isEdit ? "edit/{$id}" : "create"));
        }

        try {
            if ($isEdit) {
                $success = $model->mettreAJourParIdentifiant($id, $itemData);
            } else {
                $success = $model->creer($itemData);
            }

            if (!$success) {
                throw new OperationImpossibleException("Échec de l'" . ($isEdit ? "mise à jour" : "création") . " de l'élément de référentiel.");
            }
            $this->setFlashMessage('success', 'Élément de référentiel ' . ($isEdit ? 'modifié' : 'ajouté') . ' avec succès.');
            $this->redirect("/dashboard/admin/referentiels/{$referentielCode}/list");

        } catch (DoublonException $e) {
            $this->setFlashMessage('error', 'Erreur: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/referentiels/{$referentielCode}/" . ($isEdit ? "edit/{$id}" : "create"));
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect("/dashboard/admin/referentiels/{$referentielCode}/list");
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Opération impossible: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/referentiels/{$referentielCode}/" . ($isEdit ? "edit/{$id}" : "create"));
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
            $this->redirect("/dashboard/admin/referentiels/{$referentielCode}/" . ($isEdit ? "edit/{$id}" : "create"));
        }
    }

    /**
     * Supprime un élément de référentiel.
     * @param string $referentielCode Le code du référentiel.
     * @param string $id L'ID de l'élément à supprimer.
     */
    public function deleteItem(string $referentielCode, string $id): void
    {
        $this->requirePermission('TRAIT_ADMIN_REFERENTIELS_SUPPRIMER');

        if (!isset(self::REFERENTIAL_MAP[$referentielCode])) {
            $this->setFlashMessage('error', "Référentiel '{$referentielCode}' non trouvé.");
            $this->redirect('/dashboard/admin/referentiels');
            return;
        }

        try {
            $modelClass = self::REFERENTIAL_MAP[$referentielCode];
            $model = $this->getModelInstance($modelClass);

            if (!$model->supprimerParIdentifiant($id)) {
                throw new OperationImpossibleException("Échec de la suppression de l'élément de référentiel.");
            }
            $this->setFlashMessage('success', 'Élément de référentiel supprimé avec succès.');
        } catch (ElementNonTrouveException $e) {
            $this->setFlashMessage('error', $e->getMessage());
        } catch (OperationImpossibleException $e) {
            $this->setFlashMessage('error', 'Impossible de supprimer cet élément : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Une erreur inattendue est survenue: ' . $e->getMessage());
        }
        $this->redirect("/dashboard/admin/referentiels/{$referentielCode}/list");
    }

    /**
     * Méthode utilitaire pour obtenir une instance de modèle via le conteneur.
     * @param string $modelClass Le nom pleinement qualifié de la classe du modèle.
     * @return BaseModel
     * @throws \Exception Si le modèle ne peut pas être instancié ou n'est pas un BaseModel.
     */
    private function getModelInstance(string $modelClass): BaseModel
    {
        // Accéder au conteneur (normalement injecté ou accessible globalement)
        // Pour l'exemple ici, nous allons récupérer PDO et instancier.
        // Idéalement, le conteneur lui-même aurait une méthode `get()` qui pourrait instancier les modèles.
        // On va simuler l'accès à PDO via authService->getUtilisateurModel()->getDb()
        $pdo = $this->authService->getUtilisateurModel()->getDb();
        if (!class_exists($modelClass)) {
            throw new \RuntimeException("La classe de modèle {$modelClass} n'existe pas.");
        }
        $model = new $modelClass($pdo);
        if (!$model instanceof BaseModel) {
            throw new \RuntimeException("La classe de modèle {$modelClass} doit étendre BaseModel.");
        }
        return $model;
    }
}