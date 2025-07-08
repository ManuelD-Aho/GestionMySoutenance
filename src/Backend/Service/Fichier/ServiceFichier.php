<?php

namespace App\Backend\Service\Fichier;

use App\Backend\Service\Supervision\ServiceSupervisionInterface;
use App\Backend\Service\Securite\ServiceSecuriteInterface;
use PDO;

class ServiceFichier implements ServiceFichierInterface
{
    private PDO $db;
    private ServiceSupervisionInterface $supervisionService;
    private ServiceSecuriteInterface $securiteService;
    private string $uploadPath;

    public function __construct(
        PDO $db,
        ServiceSupervisionInterface $supervisionService,
        ServiceSecuriteInterface $securiteService
    ) {
        $this->db = $db;
        $this->supervisionService = $supervisionService;
        $this->securiteService = $securiteService;
        $this->uploadPath = ROOT_PATH . '/uploads/system/';

        // Créer le dossier d'upload s'il n'existe pas
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    public function getAllFiles(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $whereClause = "WHERE 1=1";
        $params = [];

        if (!empty($filters['type'])) {
            $whereClause .= " AND f.type_mime LIKE :type";
            $params['type'] = $filters['type'] . '%';
        }

        if (!empty($filters['extension'])) {
            $whereClause .= " AND f.nom_original LIKE :extension";
            $params['extension'] = '%.' . $filters['extension'];
        }

        if (!empty($filters['taille_min'])) {
            $whereClause .= " AND f.taille >= :taille_min";
            $params['taille_min'] = (int)$filters['taille_min'] * 1024; // Convertir en bytes
        }

        if (!empty($filters['taille_max'])) {
            $whereClause .= " AND f.taille <= :taille_max";
            $params['taille_max'] = (int)$filters['taille_max'] * 1024; // Convertir en bytes
        }

        if (!empty($filters['date_debut'])) {
            $whereClause .= " AND f.date_upload >= :date_debut";
            $params['date_debut'] = $filters['date_debut'];
        }

        if (!empty($filters['date_fin'])) {
            $whereClause .= " AND f.date_upload <= :date_fin";
            $params['date_fin'] = $filters['date_fin'];
        }

        // Compter le total
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM fichiers_systeme f $whereClause");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Récupérer les fichiers avec pagination
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("
            SELECT 
                f.*,
                u.nom as uploaded_by_nom,
                u.prenom as uploaded_by_prenom
            FROM fichiers_systeme f
            LEFT JOIN utilisateur u ON f.uploaded_by = u.numero_utilisateur
            $whereClause
            ORDER BY f.date_upload DESC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'files' => $files,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
                'items_per_page' => $limit
            ]
        ];
    }

    public function uploadFile(array $fileData, array $metadata = []): array
    {
        try {
            // Validation du fichier
            if (!$this->validateFile($fileData)) {
                return ['success' => false, 'message' => 'Fichier invalide'];
            }

            // Générer un nom unique
            $fileName = $this->generateUniqueFileName($fileData['name']);
            $filePath = $this->uploadPath . $fileName;

            // Déplacer le fichier
            if (!move_uploaded_file($fileData['tmp_name'], $filePath)) {
                return ['success' => false, 'message' => 'Erreur lors du déplacement du fichier'];
            }

            // Enregistrer en base
            $fileId = $this->saveFileToDatabase($fileData, $fileName, $filePath, $metadata);

            return ['success' => true, 'file_id' => $fileId, 'file_path' => $filePath];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function validateFile(array $fileData): bool
    {
        $allowedTypes = $this->getAllowedMimeTypes();
        $maxSize = $this->getMaxFileSize();

        return in_array($fileData['type'], $allowedTypes) &&
            $fileData['size'] <= $maxSize &&
            $fileData['error'] === UPLOAD_ERR_OK;
    }

    private function generateUniqueFileName(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }

    private function saveFileToDatabase(array $fileData, string $fileName, string $filePath, array $metadata): string
    {
        $fileId = 'FILE_' . date('Y') . '_' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);

        $stmt = $this->db->prepare("
            INSERT INTO fichiers_systeme (
                id_fichier, nom_original, nom_stockage, chemin, 
                taille, type_mime, uploaded_by, description, 
                categorie, public, date_upload
            ) VALUES (
                :id_fichier, :nom_original, :nom_stockage, :chemin,
                :taille, :type_mime, :uploaded_by, :description,
                :categorie, :public, NOW()
            )
        ");

        $stmt->execute([
            'id_fichier' => $fileId,
            'nom_original' => $fileData['name'],
            'nom_stockage' => $fileName,
            'chemin' => $filePath,
            'taille' => $fileData['size'],
            'type_mime' => $fileData['type'],
            'uploaded_by' => $metadata['uploaded_by'] ?? null,
            'description' => $metadata['description'] ?? '',
            'categorie' => $metadata['categorie'] ?? 'GENERAL',
            'public' => $metadata['public'] ? 1 : 0
        ]);

        return $fileId;
    }

    public function downloadFile(string $id): void
    {
        $file = $this->getFileDetails($id);
        if (!$file || !file_exists($file['chemin'])) {
            throw new \RuntimeException("Fichier non trouvé");
        }

        header('Content-Type: ' . $file['type_mime']);
        header('Content-Disposition: attachment; filename="' . $file['nom_original'] . '"');
        header('Content-Length: ' . filesize($file['chemin']));

        readfile($file['chemin']);
        exit;
    }

    public function deleteFile(string $id): bool
    {
        $file = $this->getFileDetails($id);
        if (!$file) {
            return false;
        }

        // Supprimer le fichier physique
        if (file_exists($file['chemin'])) {
            unlink($file['chemin']);
        }

        // Supprimer de la base
        $stmt = $this->db->prepare("DELETE FROM fichiers_systeme WHERE id_fichier = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getFileDetails(string $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                f.*,
                u.nom as uploaded_by_nom,
                u.prenom as uploaded_by_prenom
            FROM fichiers_systeme f
            LEFT JOIN utilisateur u ON f.uploaded_by = u.numero_utilisateur
            WHERE f.id_fichier = :id
        ");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getFileStats(): array
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_files,
                SUM(taille) as total_size,
                AVG(taille) as average_size,
                COUNT(CASE WHEN public = 1 THEN 1 END) as public_files,
                COUNT(CASE WHEN public = 0 THEN 1 END) as private_files
            FROM fichiers_systeme
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllowedMimeTypes(): array
    {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png',
            'image/gif',
            'text/plain',
            'text/csv'
        ];
    }

    public function getAllowedExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'csv'];
    }

    public function getMaxFileSize(): int
    {
        return 10 * 1024 * 1024; // 10MB
    }

    public function isFileUsed(string $id): bool
    {
        // Vérifier si le fichier est référencé ailleurs
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM rapport_etudiant 
            WHERE fichier_rapport = :file_id
            OR fichier_annexe = :file_id
        ");
        $stmt->execute(['file_id' => $id]);

        return $stmt->fetchColumn() > 0;
    }

    public function updateFileMetadata(string $id, array $metadata): bool
    {
        $stmt = $this->db->prepare("
            UPDATE fichiers_systeme 
            SET description = :description,
                categorie = :categorie,
                public = :public,
                tags = :tags
            WHERE id_fichier = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'description' => $metadata['description'] ?? '',
            'categorie' => $metadata['categorie'] ?? 'GENERAL',
            'public' => $metadata['public'] ? 1 : 0,
            'tags' => $metadata['tags'] ?? ''
        ]);
    }

    public function getFileUsage(string $id): array
    {
        // Retourner où le fichier est utilisé
        return [];
    }

    public function getFileMetadata(string $id): array
    {
        $stmt = $this->db->prepare("
            SELECT description, categorie, public, tags, date_upload 
            FROM fichiers_systeme 
            WHERE id_fichier = :id
        ");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function scanAllFiles(): array
    {
        // Simulation d'un scan antivirus
        return [
            'scanned' => 150,
            'threats' => 0,
            'quarantined' => 0
        ];
    }

    public function cleanupOrphanFiles(): array
    {
        // Nettoyer les fichiers orphelins
        $count = 0;

        // Fichiers en base mais pas sur disque
        $stmt = $this->db->query("SELECT id_fichier, chemin FROM fichiers_systeme");
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($files as $file) {
            if (!file_exists($file['chemin'])) {
                $this->deleteFile($file['id_fichier']);
                $count++;
            }
        }

        return ['deleted' => $count];
    }
}