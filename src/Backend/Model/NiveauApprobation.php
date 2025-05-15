<?php
class NiveauApprobation {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM niveau_approbation");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM niveau_approbation WHERE id_niveau_approbation = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO niveau_approbation (lib_niveau_approbation)
            VALUES (:lib_niveau_approbation)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE niveau_approbation
            SET lib_niveau_approbation = :lib_niveau_approbation
            WHERE id_niveau_approbation = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM niveau_approbation WHERE id_niveau_approbation = :id");
        return $stmt->execute(['id' => $id]);
    }
}
