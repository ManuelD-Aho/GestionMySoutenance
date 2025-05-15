<?php
class AnneeAcademique {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM annee_academique");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM annee_academique WHERE id_annee_academique = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO annee_academique (lib_annee_academique)
            VALUES (:lib_annee_academique)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE annee_academique
            SET lib_annee_academique = :lib_annee_academique
            WHERE id_annee_academique = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM annee_academique WHERE id_annee_academique = :id");
        return $stmt->execute(['id' => $id]);
    }
}
