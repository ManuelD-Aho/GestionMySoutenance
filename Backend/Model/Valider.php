<?php
class Valider {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM valider");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM valider WHERE id_enseignant = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO valider (id_rapport_etudiant, date_validation, commentaire_validation)
            VALUES (:id_rapport_etudiant, :date_validation, :commentaire_validation)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE valider
            SET id_rapport_etudiant = :id_rapport_etudiant, date_validation = :date_validation, commentaire_validation = :commentaire_validation
            WHERE id_enseignant = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM valider WHERE id_enseignant = :id");
        return $stmt->execute(['id' => $id]);
    }
}
