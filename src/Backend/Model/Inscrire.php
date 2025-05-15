<?php
class Inscrire {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM inscrire");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM inscrire WHERE id_etudiant = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO inscrire (id_niveau_etude, id_annee_academique, montant_inscription)
            VALUES (:id_niveau_etude, :id_annee_academique, :montant_inscription)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE inscrire
            SET id_niveau_etude = :id_niveau_etude, id_annee_academique = :id_annee_academique, montant_inscription = :montant_inscription
            WHERE id_etudiant = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM inscrire WHERE id_etudiant = :id");
        return $stmt->execute(['id' => $id]);
    }
}
