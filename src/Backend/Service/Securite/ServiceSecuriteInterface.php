<?php

namespace App\Backend\Service\Securite;

interface ServiceSecuriteInterface
{
    /**
     * Valide les données d'entrée selon les règles de sécurité.
     * @param array $donnees Les données à valider.
     * @param array $regles Les règles de validation.
     * @return bool Vrai si validation réussie.
     * @throws \App\Backend\Exception\ValidationException En cas d'erreur de validation.
     */
    public function validerDonnees(array $donnees, array $regles): bool;

    /**
     * Crypte une chaîne de caractères sensible.
     * @param string $donnees Les données à crypter.
     * @return string Les données cryptées.
     */
    public function crypterDonnees(string $donnees): string;

    /**
     * Décrypte une chaîne de caractères.
     * @param string $donneesChiffrees Les données chiffrées.
     * @return string Les données décryptées.
     */
    public function decrypterDonnees(string $donneesChiffrees): string;

    /**
     * Génère un token sécurisé.
     * @param int $longueur La longueur du token.
     * @return string Le token généré.
     */
    public function genererTokenSecurise(int $longueur = 32): string;

    /**
     * Vérifie l'intégrité d'un fichier téléchargé.
     * @param array $fichier Les informations du fichier ($_FILES).
     * @return bool Vrai si le fichier est sûr.
     */
    public function verifierIntegriteFichier(array $fichier): bool;

    /**
     * Nettoie et sécurise une entrée utilisateur.
     * @param string $entree L'entrée à nettoyer.
     * @param string $type Le type de nettoyage (html, sql, xss).
     * @return string L'entrée nettoyée.
     */
    public function nettoyerEntree(string $entree, string $type = 'html'): string;

    /**
     * Journalise un événement de sécurité.
     * @param string $evenement Le type d'événement.
     * @param string $description La description de l'événement.
     * @param array $contexte Le contexte supplémentaire.
     * @return void
     */
    public function journaliserEvenementSecurite(string $evenement, string $description, array $contexte = []): void;

    /**
     * Vérifie si une adresse IP est dans la liste blanche.
     * @param string $ip L'adresse IP à vérifier.
     * @return bool Vrai si l'IP est autorisée.
     */
    public function verifierIpAutorisee(string $ip): bool;

    /**
     * Applique des restrictions de débit (rate limiting).
     * @param string $identifiant L'identifiant unique (IP, utilisateur, etc.).
     * @param int $limite Le nombre maximum de requêtes.
     * @param int $periode La période en secondes.
     * @return bool Vrai si la limite n'est pas dépassée.
     */
    public function appliquerLimiteDebit(string $identifiant, int $limite, int $periode): bool;
}