<?php
namespace Backend\Model;

use Backend\Model\BaseModel; // Added use statement for BaseModel

class Enseignant extends BaseModel { // Extends BaseModel
    protected string $table = 'enseignant'; // Define table name
    protected string $primaryKey = 'id_enseignant'; // Define primary key

    // Constructor and $pdo property are removed, handled by BaseModel

    // getAll() method is removed, functionality covered by findAll() from BaseModel
    // getById($id) method is removed, functionality covered by find($id) from BaseModel

    /**
     * Creates a new enseignant record.
     *
     * @param array $data Data for the new record. 
     *                    Expected keys can include: 'nom', 'prenom', 'id_utilisateur'.
     * @return string|false The ID of the newly created record on success, false on failure.
     */
    public function create(array $data): string|false // Signature updated
    {
        // Logic for preparing SQL and executing is now in BaseModel's create method
        return parent::create($data);
    }

    /**
     * Updates an existing enseignant record.
     *
     * @param int $id The ID of the record to update.
     * @param array $data Data to update the record with. 
     *                    Expected keys can match any column in 'enseignant'.
     * @return bool True on success, false on failure.
     */
    public function update(int $id, array $data): bool // Signature updated
    {
        // Logic for preparing SQL and executing is now in BaseModel's update method
        return parent::update($id, $data);
    }

    /**
     * Deletes an enseignant record.
     *
     * @param int $id The ID of the record to delete.
     * @return bool True on success, false on failure.
     */
    public function delete(int $id): bool // Signature updated
    {
        // Logic for preparing SQL and executing is now in BaseModel's delete method
        return parent::delete($id);
    }
}
