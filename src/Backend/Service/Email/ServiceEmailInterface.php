<?php

namespace App\Backend\Service\Email;

use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\EmailException;

interface ServiceEmailInterface
{
    /**
     * Envoie un email.
     * @param array $emailData Données de l'email: destinataire_email, sujet, corps_html (optionnel), corps_texte (optionnel).
     * @return bool Vrai si l'email a été envoyé avec succès.
     * @throws EmailException En cas d'échec de l'envoi de l'email.
     */
    public function envoyerEmail(array $emailData): bool;

    /**
     * Envoie un email en utilisant un modèle stocké en base de données.
     * @param string $destinataireEmail L'adresse email du destinataire.
     * @param string $modeleCode L'ID du modèle de notification à utiliser.
     * @param array $variablesModele Tableau associatif des variables à remplacer dans le modèle.
     * @return bool Vrai si l'email a été envoyé avec succès.
     * @throws ElementNonTrouveException Si le modèle d'email n'est pas trouvé.
     * @throws EmailException En cas d'échec de l'envoi de l'email.
     */
    public function envoyerEmailAvecModele(string $destinataireEmail, string $modeleCode, array $variablesModele = []): bool;
}