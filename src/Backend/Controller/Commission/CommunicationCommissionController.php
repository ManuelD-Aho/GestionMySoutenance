<?php
namespace App\Backend\Controller\Commission;

use App\Backend\Controller\BaseController;
use App\Backend\Exception\ValidationException;
use App\Backend\Service\Authentication\ServiceAuthentication;
use App\Backend\Service\Permissions\ServicePermissions;
use App\Backend\Util\FormValidator;
use App\Backend\Service\Messagerie\ServiceMessagerie; // Importer le service
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

class CommunicationCommissionController extends BaseController
{
    private ServiceMessagerie $messagerieService;

    public function __construct(
        ServiceAuthentication $authService,
        ServicePermissions    $permissionService,
        FormValidator         $validator,
        ServiceMessagerie     $messagerieService // Injection
    ) {
        parent::__construct($authService, $permissionService, $validator);
        $this->messagerieService = $messagerieService;
    }

    /**
     * Affiche l'interface de communication pour la commission.
     * Liste les conversations et affiche les messages d'une conversation sélectionnée.
     * @param string|null $idConversation L'ID de la conversation à afficher.
     */
    public function index(?string $idConversation = null): void
    {
        $this->requirePermission('TRAIT_COMMISSION_COMMUNICATION_ACCEDER'); // Exiger la permission

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
            $numeroUtilisateur = $currentUser['numero_utilisateur'];

            $conversations = $this->messagerieService->listerConversationsPourUtilisateur($numeroUtilisateur);
            $currentConversation = null;
            $messages = [];

            if ($idConversation) {
                $currentConversation = $this->messagerieService->getConversationDetails($idConversation); // Méthode à ajouter au service
                if (!$currentConversation) {
                    throw new ElementNonTrouveException("Conversation non trouvée.");
                }
                // Vérifier que l'utilisateur est bien participant de cette conversation
                if (!$this->messagerieService->estParticipant($idConversation, $numeroUtilisateur)) { // Méthode à ajouter au service
                    throw new OperationImpossibleException("Vous n'êtes pas participant de cette conversation.");
                }
                $messages = $this->messagerieService->recupererMessagesDuneConversation($idConversation);
                // Marquer les messages comme lus (optionnel, peut se faire par AJAX)
                // $this->messagerieService->marquerMessagesCommeLus($numeroUtilisateur, array_column($messages, 'id_message_chat'));
            } elseif (!empty($conversations)) {
                // Si aucune conversation n'est sélectionnée, charger la première par défaut
                $idConversation = $conversations[0]['id_conversation'];
                $this->redirect("/dashboard/commission/communication/{$idConversation}");
                return;
            }

            $data = [
                'page_title' => 'Messagerie Commission',
                'conversations' => $conversations,
                'current_conversation' => $currentConversation,
                'messages' => $messages,
                'current_user_id' => $numeroUtilisateur // Pour afficher l'expéditeur
            ];
            $this->render('common/chat_interface', $data); // Utilise la vue générique de chat
        } catch (\Exception $e) {
            $this->setFlashMessage('error', "Erreur lors du chargement de la messagerie: " . $e->getMessage());
            $this->redirect('/dashboard/commission');
        }
    }

    /**
     * Traite l'envoi d'un nouveau message.
     * @param string $idConversation L'ID de la conversation.
     */
    public function sendMessage(string $idConversation): void
    {
        $this->requirePermission('TRAIT_COMMISSION_COMMUNICATION_ENVOYER_MESSAGE');
        if (!$this->isPostRequest()) {
            $this->redirect("/dashboard/commission/communication/{$idConversation}");
        }

        $contenuMessage = $this->getRequestData('message_content');
        $rules = ['message_content' => 'required|string|min:1|max:1000']; // Longueur max du message
        $this->validator->validate(['message_content' => $contenuMessage], $rules);

        if (!$this->validator->isValid()) {
            $this->setFlashMessage('error', implode('<br>', $this->validator->getErrors()));
            $this->redirect("/dashboard/commission/communication/{$idConversation}");
        }

        try {
            $currentUser = $this->getCurrentUser();
            if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
            $numeroUtilisateur = $currentUser['numero_utilisateur'];

            $this->messagerieService->envoyerMessageDansConversation($idConversation, $numeroUtilisateur, $contenuMessage);
            $this->setFlashMessage('success', 'Message envoyé.');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur envoi message: ' . $e->getMessage());
        }
        $this->redirect("/dashboard/commission/communication/{$idConversation}");
    }

    /**
     * Gère la création d'une nouvelle conversation (directe ou de groupe).
     * Peut être un formulaire ou une action directe.
     */
    public function createConversation(): void
    {
        $this->requirePermission('TRAIT_COMMISSION_COMMUNICATION_CREER_CONVERSATION');

        if ($this->isPostRequest()) {
            $this->handleCreateConversation();
        } else {
            // Afficher un formulaire pour choisir les participants, le nom du groupe, etc.
            // $usersList = $this->authService->listerUtilisateursAvecProfils(); // Lister tous les utilisateurs
            $data = [
                'page_title' => 'Créer une Nouvelle Conversation',
                // 'users_list' => $usersList,
                'form_action' => '/dashboard/commission/communication/create'
            ];
            $this->render('Commission/Communication/create_conversation_form', $data); // Créer cette vue
        }
    }

    /**
     * Traite la soumission du formulaire de création de conversation.
     */
    private function handleCreateConversation(): void
    {
        $type = $this->getRequestData('conversation_type'); // 'direct' ou 'groupe'
        $participants = $this->getRequestData('participants', []); // Tableau des numéros d'utilisateur
        $nomGroupe = $this->getRequestData('group_name'); // Pour les conversations de groupe

        $currentUser = $this->getCurrentUser();
        if (!$currentUser) { throw new ElementNonTrouveException("Utilisateur non trouvé."); }
        $numeroCreateur = $currentUser['numero_utilisateur'];

        try {
            if ($type === 'direct') {
                if (count($participants) !== 1) { // Une conversation directe a un seul autre participant
                    throw new OperationImpossibleException("Veuillez sélectionner exactement un autre participant pour une conversation directe.");
                }
                $idConversation = $this->messagerieService->demarrerOuRecupererConversationDirecte($numeroCreateur, $participants[0]);
            } elseif ($type === 'groupe') {
                $rules = ['group_name' => 'required|string|min:3|max:255'];
                $this->validator->validate(['group_name' => $nomGroupe], $rules);
                if (!$this->validator->isValid()) {
                    throw new ValidationException(implode('<br>', $this->validator->getErrors()));
                }
                if (count($participants) < 1) { // Un groupe doit avoir au moins un autre participant
                    throw new OperationImpossibleException("Veuillez sélectionner au moins un participant pour la conversation de groupe.");
                }
                $idConversation = $this->messagerieService->creerNouvelleConversationDeGroupe($nomGroupe, $numeroCreateur, $participants);
            } else {
                throw new OperationImpossibleException("Type de conversation invalide.");
            }
            $this->setFlashMessage('success', 'Conversation créée avec succès.');
            $this->redirect("/dashboard/commission/communication/{$idConversation}");
        } catch (ValidationException $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/dashboard/commission/communication/create');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Erreur création conversation: ' . $e->getMessage());
            $this->redirect('/dashboard/commission/communication/create');
        }
    }

    // Les méthodes update($id) et delete($id) génériques du template initial sont à supprimer.
    // Les ajouts/retraits de participants sont gérés par des actions spécifiques, pas un CRUD générique sur la conversation.
    /*
    public function update($id): void {}
    public function delete($id): void {}
    */
}