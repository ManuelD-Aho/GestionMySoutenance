<?php
class Ue {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM ue");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM ue WHERE id_ue = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO ue (lib_ue)
            VALUES (:lib_ue)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE ue
            SET lib_ue = :lib_ue
            WHERE id_ue = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM ue WHERE id_ue = :id");
        return $stmt->execute(['id' => $id]);
    }
}
