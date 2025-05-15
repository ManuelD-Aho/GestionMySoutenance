<?php
class NiveauAccesDonne {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM niveau_acces_donne");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM niveau_acces_donne WHERE id_niveau_acces_donne = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO niveau_acces_donne (lib_niveau_acces_donne)
            VALUES (:lib_niveau_acces_donne)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE niveau_acces_donne
            SET lib_niveau_acces_donne = :lib_niveau_acces_donne
            WHERE id_niveau_acces_donne = :id
        ");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM niveau_acces_donne WHERE id_niveau_acces_donne = :id");
        return $stmt->execute(['id' => $id]);
    }
}
