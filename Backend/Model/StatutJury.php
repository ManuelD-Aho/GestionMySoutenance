<?php
class StatutJury {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM statut_jury");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM statut_jury WHERE id_statut_jury = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO statut_jury (lib_statut_jury)
            VALUES (:lib_statut_jury)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE statut_jury
            SET lib_statut_jury = :lib_statut_jury
            WHERE id_statut_jury = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM statut_jury WHERE id_statut_jury = :id");
        return $stmt->execute(['id' => $id]);
    }
}
