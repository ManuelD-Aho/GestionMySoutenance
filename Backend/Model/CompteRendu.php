<?php
class CompteRendu {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM compte_rendu");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM compte_rendu WHERE id_compte_rendu = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO compte_rendu (lib_compte_rendu)
            VALUES (:lib_compte_rendu)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE compte_rendu
            SET lib_compte_rendu = :lib_compte_rendu
            WHERE id_compte_rendu = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM compte_rendu WHERE id_compte_rendu = :id");
        return $stmt->execute(['id' => $id]);
    }
}
