<?php

namespace App\Backend\Model;

use PDO;

class Recevoir extends BaseModel
{
    protected string $table = 'recevoir';
    // CORRECTION : La clé primaire est 'id_reception' (VARCHAR) selon la DDL, et non une clé composite.
    protected string|array $primaryKey = 'id_reception';

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Trouve une entrée de réception de notification spécifique par ses clés logiques.
     * NOTE : Cette méthode reste utile pour la logique métier, mais n'utilise pas la clé primaire de la table.
     * @param string $numeroUtilisateur Le numéro de l'utilisateur.
     * @param string $idNotification L'ID de la notification.
     * @param string $dateReception La date et heure de réception (format YYYY-MM-DD HH:MM:SS).
     * @param array $colonnes Les colonnes à sélectionner.
     * @return array|null Les données de réception ou null si non trouvée.
     */
    public function trouverReceptionParClesLogiques(string $numeroUtilisateur, string $idNotification, string $dateReception, array $colonnes = ['*']): ?array
    {
        return $this->trouverUnParCritere([
            'numero_utilisateur' => $numeroUtilisateur,
            'id_notification' => $idNotification,
            'date_reception' => $dateReception
        ], $colonnes);
    }

    /**
     * Met à jour une entrée de réception de notification spécifique par sa clé primaire.
     * @param string $idReception L'ID de la réception.
     * @param array $donnees Les données à mettre à jour.
     * @return bool Vrai si la mise à jour a réussi, faux sinon.
     */
    public function mettreAJourReception(string $idReception, array $donnees): bool
    {
        return $this->mettreAJourParIdentifiant($idReception, $donnees);
    }

    /**
     * Supprime une entrée de réception de notification spécifique par sa clé primaire.
     * @param string $idReception L'ID de la réception.
     * @return bool Vrai si la suppression a réussi, faux sinon.
     */
    public function supprimerReception(string $idReception): bool
    {
        return $this->supprimerParIdentifiant($idReception);
    }
}