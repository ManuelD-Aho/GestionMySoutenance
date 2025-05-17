<?php

namespace Backend\Model; // Ajout du namespace

use PDO; // Ajout de use PDO
use Backend\Model\BaseModel; // Ajout de use BaseModel

class Action extends BaseModel { // Renommer la classe avec suffixe "Model" et hériter de BaseModel

    protected string $table = 'action'; // Nom de la table
    protected string $primaryKey = 'id_action'; // Clé primaire de la table

    // Le constructeur qui appelle le parent est automatiquement hérité
    // si aucune logique spécifique n'est ajoutée ici.
    // Si tu as besoin d'initialiser quelque chose de spécifique à ActionModel :
    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
        // Logique spécifique au constructeur de ActionModel si nécessaire
    }

    // Les méthodes getAll, getById (qui devient find), create, update, delete
    // sont maintenant héritées de BaseModel.
    // Tu n'as plus besoin de les redéfinir ici sauf si tu as une logique spécifique.

    // Par exemple, si la méthode getById avait une logique particulière non couverte par find(int $id),
    // tu devrais l'adapter ou la conserver.
    // Dans ce cas, les méthodes originales de Action.php sont des CRUD standard
    // et sont donc bien couvertes par BaseModel.
}