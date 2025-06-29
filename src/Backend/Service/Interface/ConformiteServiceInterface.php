<?php

declare(strict_types=1);

namespace App\Backend\Service\Interface;

use App\Backend\Exception\ElementNonTrouveException;
use App\Backend\Exception\OperationImpossibleException;

interface ConformiteServiceInterface
{
    /**
     * Enregistre le verdict de conformité (conforme/non-conforme) pour un rapport.
     *
     * @param string $idRapport L'ID du rapport.
     * @param string $idAgent L'ID de l'agent de conformité.
     * @param string $idStatut Le statut de conformité.
     * @param string|null $commentaire Le commentaire en cas de non-conformité.
     * @return bool True en cas de succès.
     * @throws ElementNonTrouveException Si le rapport ou l'agent n'existe pas.
     */
    public function soumettreVerdictConformite(string $idRapport, string $idAgent, string $idStatut, ?string $commentaire): bool;

    /**
     * Enregistre les résultats détaillés de la checklist de conformité pour un rapport.
     *
     * @param string $idRapport L'ID du rapport.
     * @param array $detailsCriteres Un tableau associatif ['id_critere' => 'statut'].
     * @return bool True en cas de succès.
     */
    public function enregistrerDetailsChecklist(string $idRapport, array $detailsCriteres): bool;

    /**
     * Liste les rapports en attente de vérification de conformité.
     *
     * @return array La liste des rapports.
     */
    public function listerRapportsAExaminer(): array;

    /**
     * Liste l'historique des rapports traités par un agent de conformité.
     *
     * @param string $idAgent L'ID de l'agent.
     * @return array L'historique des rapports.
     */
    public function listerRapportsTraitesParAgent(string $idAgent): array;

    /**
     * Récupère les détails de la vérification de conformité pour un rapport spécifique.
     *
     * @param string $idRapport L'ID du rapport.
     * @return array|null Les détails de la conformité ou null si non trouvés.
     */
    public function getDetailsConformiteRapport(string $idRapport): ?array;

    /**
     * Change le statut du rapport à "En attente d'évaluation" et notifie la commission.
     *
     * @param string $idRapport L'ID du rapport.
     * @return bool True en cas de succès.
     * @throws OperationImpossibleException Si le rapport n'est pas à l'état 'Conforme'.
     */
    public function transmettreRapportACommission(string $idRapport): bool;
}