<?php

namespace App\Backend\Service\Email;

/**
 * Interface ServiceEmailInterface
 * Définit le contrat pour un service d'envoi d'emails.
 */
interface ServiceEmailInterface
{
    /**
     * Envoie un email.
     *
     * @param string $destinataire L'adresse email du destinataire.
     * @param string $sujet Le sujet de l'email.
     * @param string $corpsMessage Le contenu du message (peut être du texte brut ou HTML selon l'implémentation).
     * @param array $entetes Optionnel. Tableau associatif d'en-têtes supplémentaires (ex: ['From' => 'expediteur@example.com', 'Reply-To' => 'reponse@example.com']).
     * @param bool $estHtml Optionnel. True si le corps du message est en HTML, false pour texte brut. Par défaut à false.
     * @return bool True si l'email a été envoyé avec succès (ou mis en file d'attente), false sinon.
     * @throws \App\Backend\Exception\EmailException Si une erreur se produit lors de la tentative d'envoi.
     */
    public function envoyerEmail(
        string $destinataire,
        string $sujet,
        string $corpsMessage,
        array $entetes = [],
        bool $estHtml = false
    ): bool;

    /**
     * Envoie un email en utilisant un modèle (template).
     *
     * @param string $destinataire L'adresse email du destinataire.
     * @param string $sujet Le sujet de l'email.
     * @param string $nomModele Le nom ou l'identifiant du modèle d'email à utiliser.
     * @param array $donneesModele Tableau associatif des données à injecter dans le modèle.
     * @param array $entetes Optionnel. Tableau associatif d'en-têtes supplémentaires.
     * @return bool True si l'email a été envoyé avec succès, false sinon.
     * @throws \App\Backend\Exception\EmailException Si une erreur se produit ou si le modèle n'est pas trouvé.
     * @throws \App\Backend\Exception\ModeleNonTrouveException Si le modèle d'email spécifié n'existe pas.
     */
    public function envoyerEmailAvecModele(
        string $destinataire,
        string $sujet,
        string $nomModele,
        array $donneesModele = [],
        array $entetes = []
    ): bool;
}