<?php
class Approuver {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM approuver");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM approuver WHERE id_personnel_administratif = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO approuver (id_rapport_etudiant, date_approbation)
            VALUES (:id_rapport_etudiant, :date_approbation)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE approuver
            SET id_rapport_etudiant = :id_rapport_etudiant, date_approbation = :date_approbation
            WHERE id_personnel_administratif = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM approuver WHERE id_personnel_administratif = :id");
        return $stmt->execute(['id' => $id]);
    }
}
