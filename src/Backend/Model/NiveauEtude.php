<?php
class NiveauEtude {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM niveau_etude");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM niveau_etude WHERE id_niveau_etude = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO niveau_etude (lib_niveau_etude)
            VALUES (:lib_niveau_etude)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE niveau_etude
            SET lib_niveau_etude = :lib_niveau_etude
            WHERE id_niveau_etude = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM niveau_etude WHERE id_niveau_etude = :id");
        return $stmt->execute(['id' => $id]);
    }
}
