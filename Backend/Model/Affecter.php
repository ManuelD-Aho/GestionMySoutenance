<?php
class Affecter {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM affecter");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM affecter WHERE id_enseignant = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO affecter (id_rapport_etudiant, id_statut_jury, oui)
            VALUES (:id_rapport_etudiant, :id_statut_jury, :oui)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE affecter
            SET id_rapport_etudiant = :id_rapport_etudiant, id_statut_jury = :id_statut_jury, oui = :oui
            WHERE id_enseignant = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM affecter WHERE id_enseignant = :id");
        return $stmt->execute(['id' => $id]);
    }
}
