<?php
class Acquerir {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM acquerir");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM acquerir WHERE id_grade = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO acquerir (id_enseignant, date_acquisition)
            VALUES (:id_enseignant, :date_acquisition)
        ");
        return $stmt->execute($data);
    }

    public function update($id, $data) {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare("
            UPDATE acquerir
            SET id_enseignant = :id_enseignant, date_acquisition = :date_acquisition
            WHERE id_grade = :id
        ");
        return $stmt->execute($data);
    }
//Test
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM acquerir WHERE id_grade = :id");
        return $stmt->execute(['id' => $id]);
    }
}
