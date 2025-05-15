<?php
class TypeUtilisateur {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM type_utilisateur");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM type_utilisateur WHERE id_type_utilisateur = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO type_utilisateur (lib_type_utilisateur)
            VALUES (:lib_type_utilisateur)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE type_utilisateur
            SET lib_type_utilisateur = :lib_type_utilisateur
            WHERE id_type_utilisateur = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM type_utilisateur WHERE id_type_utilisateur = :id");
        return $stmt->execute(['id' => $id]);
    }
}
