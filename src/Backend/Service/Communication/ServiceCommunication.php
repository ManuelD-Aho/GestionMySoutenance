<?php

namespace App\Backend\Service\Communication;

use PDO;
use App\Backend\Model\Conversation;
use App\Backend\Model\MessageChat;
use App\Backend\Model\ParticipantConversation;
use App\Backend\Model\LectureMessage;
use App\Backend\Service\Messagerie\ServiceMessagerieInterface;
use App\Backend\Service\Notification\ServiceNotificationInterface;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\IdentifiantGenerator\IdentifiantGeneratorInterface;
use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\ValidationException;
use App\Backend\Exception\OperationImpossibleException;
use App\Backend\Exception\PermissionException;

class ServiceCommunication implements ServiceCommunicationInterface
{
    private PDO $db;
    private Conversation $conversationModel;
    private MessageChat $messageChatModel;
    private ParticipantConversation $participantModel;
    private LectureMessage $lectureMessageModel;
    private ServiceMessagerieInterface $messagerieService;
    private ServiceNotificationInterface $notificationService;
    private ServiceSupervisionAdminInterface $supervisionService;
    private IdentifiantGeneratorInterface $idGenerator;

    public function __construct(
        PDO $db,
        Conversation $conversationModel,
        MessageChat $messageChatModel,
        ParticipantConversation $participantModel,
        LectureMessage $lectureMessageModel,
        ServiceMessagerieInterface $messagerieService,
        ServiceNotificationInterface $notificationService,
        ServiceSupervisionAdminInterface $supervisionService,
        IdentifiantGeneratorInterface $idGenerator
    ) {
        $this->db = $db;
        $this->conversationModel = $conversationModel;
        $this->messageChatModel = $messageChatModel;
        $this->participantModel = $participantModel;
        $this->lectureMessageModel = $lectureMessageModel;
        $this->messagerieService = $messagerieService;
        $this->notificationService = $notificationService;
        $this->supervisionService = $supervisionService;
        $this->idGenerator = $idGenerator;
    }

    public function envoyerMessagePrive(string $expediteur, string $destinataire, string $sujet, string $contenu, array $options = []): string
    {
        try {
            $this->db->beginTransaction();

            // Créer ou récupérer la conversation privée
            $idConversation = $this->creerOuRecupererConversationPrivee($expediteur, $destinataire);

            // Envoyer le message
            $idMessage = $this->envoyerMessageConversation($idConversation, $expediteur, $contenu);

            // Traiter les options spéciales
            if (!empty($options['priorite']) && $options['priorite'] === 'HAUTE') {
                $this->notificationService->envoyerNotificationUrgente(
                    $destinataire,
                    'MESSAGE_PRIORITE_HAUTE',
                    "Message prioritaire de {$expediteur}: {$sujet}",
                    ['id_message' => $idMessage, 'contenu' => substr($contenu, 0, 100)]
                );
            }

            if (!empty($options['accuse_reception'])) {
                $this->demanderAccuseReception($idMessage, $expediteur);
            }

            $this->supervisionService->enregistrerAction(
                $expediteur,
                'ENVOI_MESSAGE_PRIVE',
                "Envoi d'un message privé",
                'message',
                $idMessage,
                ['destinataire' => $destinataire, 'sujet' => $sujet]
            );

            $this->db->commit();
            return $idMessage;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible d'envoyer le message: " . $e->getMessage());
        }
    }

    public function creerConversationGroupe(string $createur, array $participants, string $titre, array $parametres = []): string
    {
        try {
            $this->db->beginTransaction();

            $idConversation = $this->idGenerator->genererProchainId('conversation');
            
            $donneesConversation = [
                'id_conversation' => $idConversation,
                'titre_conversation' => $titre,
                'type_conversation' => 'GROUPE',
                'numero_utilisateur_createur' => $createur,
                'date_creation' => date('Y-m-d H:i:s'),
                'statut_conversation' => 'ACTIVE',
                'parametres' => json_encode($parametres)
            ];

            $result = $this->conversationModel->creer($donneesConversation);

            if ($result) {
                // Ajouter le créateur comme administrateur
                $this->ajouterParticipant($idConversation, $createur, 'ADMINISTRATEUR');

                // Ajouter les autres participants
                foreach ($participants as $participant) {
                    if ($participant !== $createur) {
                        $this->ajouterParticipant($idConversation, $participant, 'MEMBRE');
                    }
                }

                // Envoyer un message de bienvenue
                $messageBienvenue = "Conversation de groupe '{$titre}' créée par {$createur}";
                $this->envoyerMessageConversation($idConversation, $createur, $messageBienvenue);

                $this->supervisionService->enregistrerAction(
                    $createur,
                    'CREATION_CONVERSATION_GROUPE',
                    "Création d'une conversation de groupe",
                    'conversation',
                    $idConversation,
                    ['titre' => $titre, 'participants' => $participants]
                );
            }

            $this->db->commit();
            return $idConversation;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de créer la conversation: " . $e->getMessage());
        }
    }

    public function ajouterParticipant(string $idConversation, string $numeroUtilisateur, string $role = 'MEMBRE'): bool
    {
        try {
            $this->db->beginTransaction();

            // Vérifier que la conversation existe
            $conversation = $this->conversationModel->trouverParId($idConversation);
            if (!$conversation) {
                throw new ElementNonTrouveException("Conversation non trouvée: {$idConversation}");
            }

            // Vérifier que l'utilisateur n'est pas déjà participant
            $participantExistant = $this->participantModel->trouverParConversationEtUtilisateur($idConversation, $numeroUtilisateur);
            if ($participantExistant) {
                throw new ValidationException("L'utilisateur est déjà participant à cette conversation.");
            }

            $idParticipant = $this->idGenerator->genererProchainId('participant_conversation');
            
            $donneesParticipant = [
                'id_participant' => $idParticipant,
                'id_conversation' => $idConversation,
                'numero_utilisateur' => $numeroUtilisateur,
                'role_participant' => $role,
                'date_ajout' => date('Y-m-d H:i:s'),
                'statut_participant' => 'ACTIF'
            ];

            $result = $this->participantModel->creer($donneesParticipant);

            if ($result) {
                // Notifier le nouvel participant
                $this->notificationService->envoyerNotificationUtilisateur(
                    $numeroUtilisateur,
                    'AJOUT_CONVERSATION',
                    "Vous avez été ajouté à la conversation '{$conversation['titre_conversation']}'",
                    ['id_conversation' => $idConversation, 'role' => $role]
                );

                // Envoyer un message d'information dans la conversation
                $messageInfo = "L'utilisateur {$numeroUtilisateur} a rejoint la conversation";
                $this->envoyerMessageConversation($idConversation, 'SYSTEM', $messageInfo);

                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'AJOUT_PARTICIPANT',
                    "Ajout d'un participant à la conversation",
                    'conversation',
                    $idConversation,
                    ['participant' => $numeroUtilisateur, 'role' => $role]
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible d'ajouter le participant: " . $e->getMessage());
        }
    }

    public function retirerParticipant(string $idConversation, string $numeroUtilisateur): bool
    {
        try {
            $this->db->beginTransaction();

            $participant = $this->participantModel->trouverParConversationEtUtilisateur($idConversation, $numeroUtilisateur);
            if (!$participant) {
                throw new ElementNonTrouveException("Participant non trouvé dans cette conversation.");
            }

            $result = $this->participantModel->mettreAJour($participant['id_participant'], [
                'statut_participant' => 'RETIRE',
                'date_retrait' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                $conversation = $this->conversationModel->trouverParId($idConversation);
                
                // Notifier l'utilisateur retiré
                $this->notificationService->envoyerNotificationUtilisateur(
                    $numeroUtilisateur,
                    'RETRAIT_CONVERSATION',
                    "Vous avez été retiré de la conversation '{$conversation['titre_conversation']}'",
                    ['id_conversation' => $idConversation]
                );

                // Envoyer un message d'information dans la conversation
                $messageInfo = "L'utilisateur {$numeroUtilisateur} a quitté la conversation";
                $this->envoyerMessageConversation($idConversation, 'SYSTEM', $messageInfo);

                $this->supervisionService->enregistrerAction(
                    $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                    'RETRAIT_PARTICIPANT',
                    "Retrait d'un participant de la conversation",
                    'conversation',
                    $idConversation,
                    ['participant' => $numeroUtilisateur]
                );
            }

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de retirer le participant: " . $e->getMessage());
        }
    }

    public function envoyerMessageConversation(string $idConversation, string $expediteur, string $contenu, array $pieceJointes = []): string
    {
        try {
            $this->db->beginTransaction();

            // Vérifier que l'expéditeur est participant à la conversation
            if ($expediteur !== 'SYSTEM') {
                $participant = $this->participantModel->trouverParConversationEtUtilisateur($idConversation, $expediteur);
                if (!$participant || $participant['statut_participant'] !== 'ACTIF') {
                    throw new PermissionException("Vous n'êtes pas autorisé à envoyer des messages dans cette conversation.");
                }
            }

            $idMessage = $this->idGenerator->genererProchainId('message_chat');
            
            $donneesMessage = [
                'id_message_chat' => $idMessage,
                'id_conversation' => $idConversation,
                'numero_utilisateur_expediteur' => $expediteur,
                'contenu_message' => $contenu,
                'date_envoi' => date('Y-m-d H:i:s'),
                'statut_message' => 'ENVOYE',
                'pieces_jointes' => !empty($pieceJointes) ? json_encode($pieceJointes) : null
            ];

            $result = $this->messageChatModel->creer($donneesMessage);

            if ($result) {
                // Mettre à jour la date de dernière activité de la conversation
                $this->conversationModel->mettreAJour($idConversation, [
                    'date_derniere_activite' => date('Y-m-d H:i:s'),
                    'id_dernier_message' => $idMessage
                ]);

                // Notifier tous les participants sauf l'expéditeur
                $this->notifierParticipantsNouveauMessage($idConversation, $expediteur, $contenu);

                $this->supervisionService->enregistrerAction(
                    $expediteur,
                    'ENVOI_MESSAGE_CONVERSATION',
                    "Envoi d'un message dans une conversation",
                    'message',
                    $idMessage,
                    ['conversation' => $idConversation, 'longueur_contenu' => strlen($contenu)]
                );
            }

            $this->db->commit();
            return $idMessage;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible d'envoyer le message: " . $e->getMessage());
        }
    }

    public function marquerCommeLu(string $idMessage, string $numeroUtilisateur): bool
    {
        try {
            $this->db->beginTransaction();

            // Vérifier que le message existe
            $message = $this->messageChatModel->trouverParId($idMessage);
            if (!$message) {
                throw new ElementNonTrouveException("Message non trouvé: {$idMessage}");
            }

            // Vérifier que l'utilisateur peut lire ce message
            $participant = $this->participantModel->trouverParConversationEtUtilisateur($message['id_conversation'], $numeroUtilisateur);
            if (!$participant) {
                throw new PermissionException("Vous n'êtes pas autorisé à lire ce message.");
            }

            // Vérifier si la lecture n'existe pas déjà
            $lectureExistante = $this->lectureMessageModel->trouverParMessageEtUtilisateur($idMessage, $numeroUtilisateur);
            if ($lectureExistante) {
                return true; // Déjà marqué comme lu
            }

            $idLecture = $this->idGenerator->genererProchainId('lecture_message');
            
            $donneesLecture = [
                'id_lecture' => $idLecture,
                'id_message_chat' => $idMessage,
                'numero_utilisateur' => $numeroUtilisateur,
                'date_lecture' => date('Y-m-d H:i:s')
            ];

            $result = $this->lectureMessageModel->creer($donneesLecture);

            $this->db->commit();
            return $result;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw new OperationImpossibleException("Impossible de marquer le message comme lu: " . $e->getMessage());
        }
    }

    public function archiverConversation(string $idConversation, string $numeroUtilisateur): bool
    {
        try {
            $participant = $this->participantModel->trouverParConversationEtUtilisateur($idConversation, $numeroUtilisateur);
            if (!$participant) {
                throw new PermissionException("Vous n'êtes pas participant à cette conversation.");
            }

            $result = $this->participantModel->mettreAJour($participant['id_participant'], [
                'conversation_archivee' => true,
                'date_archivage' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $numeroUtilisateur,
                    'ARCHIVAGE_CONVERSATION',
                    "Archivage d'une conversation",
                    'conversation',
                    $idConversation
                );
            }

            return $result;

        } catch (\Exception $e) {
            throw new OperationImpossibleException("Impossible d'archiver la conversation: " . $e->getMessage());
        }
    }

    public function obtenirConversationsUtilisateur(string $numeroUtilisateur, array $filtres = []): array
    {
        $sql = "SELECT c.*, pc.role_participant, pc.conversation_archivee, pc.date_ajout,
                       (SELECT COUNT(*) FROM message_chat mc 
                        WHERE mc.id_conversation = c.id_conversation 
                        AND mc.date_envoi > COALESCE(pc.date_derniere_lecture, '1970-01-01')
                        AND mc.numero_utilisateur_expediteur != ?) as messages_non_lus,
                       (SELECT contenu_message FROM message_chat mc2 
                        WHERE mc2.id_conversation = c.id_conversation 
                        ORDER BY mc2.date_envoi DESC LIMIT 1) as dernier_message
                FROM conversation c
                JOIN participant_conversation pc ON c.id_conversation = pc.id_conversation
                WHERE pc.numero_utilisateur = ? AND pc.statut_participant = 'ACTIF'";

        $params = [$numeroUtilisateur, $numeroUtilisateur];

        // Appliquer les filtres
        if (isset($filtres['archivees']) && $filtres['archivees'] === false) {
            $sql .= " AND pc.conversation_archivee = false";
        }

        if (isset($filtres['non_lues']) && $filtres['non_lues'] === true) {
            $sql .= " HAVING messages_non_lus > 0";
        }

        $sql .= " ORDER BY c.date_derniere_activite DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenirMessagesConversation(string $idConversation, string $numeroUtilisateur, int $limite = 50, int $offset = 0): array
    {
        // Vérifier que l'utilisateur est participant
        $participant = $this->participantModel->trouverParConversationEtUtilisateur($idConversation, $numeroUtilisateur);
        if (!$participant) {
            throw new PermissionException("Vous n'êtes pas autorisé à lire cette conversation.");
        }

        $sql = "SELECT mc.*, u.login as expediteur_login,
                       (SELECT COUNT(*) FROM lecture_message lm 
                        WHERE lm.id_message_chat = mc.id_message_chat) as nb_lectures
                FROM message_chat mc
                LEFT JOIN utilisateur u ON mc.numero_utilisateur_expediteur = u.numero_utilisateur
                WHERE mc.id_conversation = ?
                AND mc.date_envoi >= ?
                ORDER BY mc.date_envoi DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $idConversation,
            $participant['date_ajout'],
            $limite,
            $offset
        ]);

        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Marquer les messages comme lus
        foreach ($messages as $message) {
            if ($message['numero_utilisateur_expediteur'] !== $numeroUtilisateur) {
                $this->marquerCommeLu($message['id_message_chat'], $numeroUtilisateur);
            }
        }

        return $messages;
    }

    public function rechercherMessages(string $numeroUtilisateur, string $terme, array $filtres = []): array
    {
        $sql = "SELECT mc.*, c.titre_conversation, u.login as expediteur_login
                FROM message_chat mc
                JOIN conversation c ON mc.id_conversation = c.id_conversation
                JOIN participant_conversation pc ON c.id_conversation = pc.id_conversation
                LEFT JOIN utilisateur u ON mc.numero_utilisateur_expediteur = u.numero_utilisateur
                WHERE pc.numero_utilisateur = ? 
                AND pc.statut_participant = 'ACTIF'
                AND mc.contenu_message LIKE ?";

        $params = [$numeroUtilisateur, '%' . $terme . '%'];

        if (!empty($filtres['conversation'])) {
            $sql .= " AND mc.id_conversation = ?";
            $params[] = $filtres['conversation'];
        }

        if (!empty($filtres['expediteur'])) {
            $sql .= " AND mc.numero_utilisateur_expediteur = ?";
            $params[] = $filtres['expediteur'];
        }

        if (!empty($filtres['date_debut'])) {
            $sql .= " AND mc.date_envoi >= ?";
            $params[] = $filtres['date_debut'];
        }

        if (!empty($filtres['date_fin'])) {
            $sql .= " AND mc.date_envoi <= ?";
            $params[] = $filtres['date_fin'];
        }

        $sql .= " ORDER BY mc.date_envoi DESC LIMIT 100";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function configurerNotifications(string $numeroUtilisateur, array $preferences): bool
    {
        try {
            // Sauvegarder les préférences de notification dans la configuration utilisateur
            $configJson = json_encode($preferences);
            
            $sql = "UPDATE utilisateur SET preferences_notification = ? WHERE numero_utilisateur = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$configJson, $numeroUtilisateur]);

            if ($result) {
                $this->supervisionService->enregistrerAction(
                    $numeroUtilisateur,
                    'CONFIGURATION_NOTIFICATIONS',
                    "Configuration des notifications de messagerie",
                    'utilisateur',
                    $numeroUtilisateur,
                    $preferences
                );
            }

            return $result;

        } catch (\Exception $e) {
            throw new OperationImpossibleException("Impossible de configurer les notifications: " . $e->getMessage());
        }
    }

    public function exporterConversation(string $idConversation, string $format = 'PDF'): string
    {
        try {
            $conversation = $this->conversationModel->trouverParId($idConversation);
            if (!$conversation) {
                throw new ElementNonTrouveException("Conversation non trouvée: {$idConversation}");
            }

            // Récupérer tous les messages
            $sql = "SELECT mc.*, u.login as expediteur_login
                    FROM message_chat mc
                    LEFT JOIN utilisateur u ON mc.numero_utilisateur_expediteur = u.numero_utilisateur
                    WHERE mc.id_conversation = ?
                    ORDER BY mc.date_envoi ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idConversation]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Générer le fichier selon le format
            $timestamp = date('Y-m-d_H-i-s');
            $nomFichier = "conversation_{$idConversation}_{$timestamp}";

            switch (strtoupper($format)) {
                case 'PDF':
                    $cheminFichier = $this->genererPDF($conversation, $messages, $nomFichier);
                    break;
                case 'HTML':
                    $cheminFichier = $this->genererHTML($conversation, $messages, $nomFichier);
                    break;
                case 'TXT':
                    $cheminFichier = $this->genererTXT($conversation, $messages, $nomFichier);
                    break;
                default:
                    throw new ValidationException("Format d'export non supporté: {$format}");
            }

            $this->supervisionService->enregistrerAction(
                $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
                'EXPORT_CONVERSATION',
                "Export d'une conversation",
                'conversation',
                $idConversation,
                ['format' => $format, 'fichier' => basename($cheminFichier)]
            );

            return $cheminFichier;

        } catch (\Exception $e) {
            throw new OperationImpossibleException("Impossible d'exporter la conversation: " . $e->getMessage());
        }
    }

    // Méthodes privées d'assistance

    private function creerOuRecupererConversationPrivee(string $utilisateur1, string $utilisateur2): string
    {
        // Rechercher une conversation privée existante entre ces deux utilisateurs
        $sql = "SELECT c.id_conversation
                FROM conversation c
                JOIN participant_conversation pc1 ON c.id_conversation = pc1.id_conversation
                JOIN participant_conversation pc2 ON c.id_conversation = pc2.id_conversation
                WHERE c.type_conversation = 'PRIVE'
                AND pc1.numero_utilisateur = ? AND pc1.statut_participant = 'ACTIF'
                AND pc2.numero_utilisateur = ? AND pc2.statut_participant = 'ACTIF'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$utilisateur1, $utilisateur2]);
        $conversationExistante = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($conversationExistante) {
            return $conversationExistante['id_conversation'];
        }

        // Créer une nouvelle conversation privée
        $idConversation = $this->idGenerator->genererProchainId('conversation');
        
        $donneesConversation = [
            'id_conversation' => $idConversation,
            'titre_conversation' => "Conversation privée",
            'type_conversation' => 'PRIVE',
            'numero_utilisateur_createur' => $utilisateur1,
            'date_creation' => date('Y-m-d H:i:s'),
            'statut_conversation' => 'ACTIVE'
        ];

        $this->conversationModel->creer($donneesConversation);

        // Ajouter les deux participants
        $this->ajouterParticipant($idConversation, $utilisateur1, 'MEMBRE');
        $this->ajouterParticipant($idConversation, $utilisateur2, 'MEMBRE');

        return $idConversation;
    }

    private function demanderAccuseReception(string $idMessage, string $expediteur): void
    {
        // Marquer le message comme nécessitant un accusé de réception
        $this->messageChatModel->mettreAJour($idMessage, [
            'accuse_reception_demande' => true
        ]);
    }

    private function notifierParticipantsNouveauMessage(string $idConversation, string $expediteur, string $contenu): void
    {
        $sql = "SELECT numero_utilisateur FROM participant_conversation 
                WHERE id_conversation = ? AND statut_participant = 'ACTIF' AND numero_utilisateur != ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idConversation, $expediteur]);
        $participants = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $conversation = $this->conversationModel->trouverParId($idConversation);
        
        foreach ($participants as $participant) {
            $this->notificationService->envoyerNotificationUtilisateur(
                $participant,
                'NOUVEAU_MESSAGE',
                "Nouveau message dans '{$conversation['titre_conversation']}'",
                [
                    'id_conversation' => $idConversation,
                    'expediteur' => $expediteur,
                    'apercu' => substr($contenu, 0, 100)
                ]
            );
        }
    }

    private function genererPDF(array $conversation, array $messages, string $nomFichier): string
    {
        // Simulation de génération PDF
        $cheminFichier = ROOT_PATH . "/tmp/{$nomFichier}.pdf";
        file_put_contents($cheminFichier, "Export PDF de la conversation {$conversation['titre_conversation']}");
        return $cheminFichier;
    }

    private function genererHTML(array $conversation, array $messages, string $nomFichier): string
    {
        // Simulation de génération HTML
        $cheminFichier = ROOT_PATH . "/tmp/{$nomFichier}.html";
        $contenu = "<html><head><title>{$conversation['titre_conversation']}</title></head><body>";
        $contenu .= "<h1>{$conversation['titre_conversation']}</h1>";
        foreach ($messages as $message) {
            $contenu .= "<div><strong>{$message['expediteur_login']}</strong> ({$message['date_envoi']}): {$message['contenu_message']}</div>";
        }
        $contenu .= "</body></html>";
        file_put_contents($cheminFichier, $contenu);
        return $cheminFichier;
    }

    private function genererTXT(array $conversation, array $messages, string $nomFichier): string
    {
        // Simulation de génération TXT
        $cheminFichier = ROOT_PATH . "/tmp/{$nomFichier}.txt";
        $contenu = "Conversation: {$conversation['titre_conversation']}\n\n";
        foreach ($messages as $message) {
            $contenu .= "[{$message['date_envoi']}] {$message['expediteur_login']}: {$message['contenu_message']}\n";
        }
        file_put_contents($cheminFichier, $contenu);
        return $cheminFichier;
    }
}