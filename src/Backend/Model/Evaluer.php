<?php
class Evaluer {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM evaluer");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM evaluer WHERE id_etudiant = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO evaluer (id_enseignant, id_ecue, date_evaluation, note)
            VALUES (:id_enseignant, :id_ecue, :date_evaluation, :note)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE evaluer
            SET id_enseignant = :id_enseignant, id_ecue = :id_ecue, date_evaluation = :date_evaluation, note = :note
            WHERE id_etudiant = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM evaluer WHERE id_etudiant = :id");
        return $stmt->execute(['id' => $id]);
    }
}
