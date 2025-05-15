<?php
class Rattacher {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM rattacher");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM rattacher WHERE id_groupe_utilisateur = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO rattacher (id_traitement)
            VALUES (:id_traitement)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE rattacher
            SET id_traitement = :id_traitement
            WHERE id_groupe_utilisateur = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM rattacher WHERE id_groupe_utilisateur = :id");
        return $stmt->execute(['id' => $id]);
    }
}
