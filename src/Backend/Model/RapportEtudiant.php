<?php
class RapportEtudiant {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM rapport_etudiant");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM rapport_etudiant WHERE id_rapport_etudiant = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO rapport_etudiant (libelle_rapport_etudiant, id_etudiant)
            VALUES (:libelle_rapport_etudiant, :id_etudiant)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE rapport_etudiant
            SET libelle_rapport_etudiant = :libelle_rapport_etudiant, id_etudiant = :id_etudiant
            WHERE id_rapport_etudiant = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM rapport_etudiant WHERE id_rapport_etudiant = :id");
        return $stmt->execute(['id' => $id]);
    }
}
