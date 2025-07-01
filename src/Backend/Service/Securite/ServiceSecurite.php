<?php

namespace App\Backend\Service\Securite;

use PDO;
use App\Backend\Service\SupervisionAdmin\ServiceSupervisionAdminInterface;
use App\Backend\Service\Logger\ServiceLoggerInterface;
use App\Backend\Exception\ValidationException;
use App\Backend\Exception\OperationImpossibleException;

class ServiceSecurite implements ServiceSecuriteInterface
{
    private PDO $db;
    private ServiceSupervisionAdminInterface $supervisionService;
    private ServiceLoggerInterface $logger;

    private const ENCRYPTION_METHOD = 'AES-256-CBC';
    private const RATE_LIMIT_TABLE = 'rate_limits';

    public function __construct(
        PDO $db,
        ServiceSupervisionAdminInterface $supervisionService,
        ServiceLoggerInterface $logger
    ) {
        $this->db = $db;
        $this->supervisionService = $supervisionService;
        $this->logger = $logger;
    }

    public function validerDonnees(array $donnees, array $regles): bool
    {
        foreach ($regles as $champ => $regle) {
            if (!isset($donnees[$champ])) {
                if (isset($regle['required']) && $regle['required']) {
                    throw new ValidationException("Le champ '{$champ}' est requis.");
                }
                continue;
            }

            $valeur = $donnees[$champ];

            // Validation de type
            if (isset($regle['type'])) {
                switch ($regle['type']) {
                    case 'email':
                        if (!filter_var($valeur, FILTER_VALIDATE_EMAIL)) {
                            throw new ValidationException("Le champ '{$champ}' doit être un email valide.");
                        }
                        break;
                    case 'numeric':
                        if (!is_numeric($valeur)) {
                            throw new ValidationException("Le champ '{$champ}' doit être numérique.");
                        }
                        break;
                    case 'string':
                        if (!is_string($valeur)) {
                            throw new ValidationException("Le champ '{$champ}' doit être une chaîne de caractères.");
                        }
                        break;
                }
            }

            // Validation de longueur
            if (isset($regle['min_length']) && strlen($valeur) < $regle['min_length']) {
                throw new ValidationException("Le champ '{$champ}' doit avoir au moins {$regle['min_length']} caractères.");
            }
            if (isset($regle['max_length']) && strlen($valeur) > $regle['max_length']) {
                throw new ValidationException("Le champ '{$champ}' ne peut pas dépasser {$regle['max_length']} caractères.");
            }

            // Validation de pattern
            if (isset($regle['pattern']) && !preg_match($regle['pattern'], $valeur)) {
                throw new ValidationException("Le champ '{$champ}' ne respecte pas le format requis.");
            }
        }

        return true;
    }

    public function crypterDonnees(string $donnees): string
    {
        try {
            $key = $this->getEncryptionKey();
            $iv = random_bytes(16);
            $encrypted = openssl_encrypt($donnees, self::ENCRYPTION_METHOD, $key, 0, $iv);
            return base64_encode($iv . $encrypted);
        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors du cryptage des données', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException('Impossible de crypter les données.');
        }
    }

    public function decrypterDonnees(string $donneesChiffrees): string
    {
        try {
            $key = $this->getEncryptionKey();
            $data = base64_decode($donneesChiffrees);
            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);
            $decrypted = openssl_decrypt($encrypted, self::ENCRYPTION_METHOD, $key, 0, $iv);
            
            if ($decrypted === false) {
                throw new \Exception('Échec du décryptage');
            }
            
            return $decrypted;
        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors du décryptage des données', ['error' => $e->getMessage()]);
            throw new OperationImpossibleException('Impossible de décrypter les données.');
        }
    }

    public function genererTokenSecurise(int $longueur = 32): string
    {
        return bin2hex(random_bytes($longueur / 2));
    }

    public function verifierIntegriteFichier(array $fichier): bool
    {
        if (!isset($fichier['tmp_name']) || !isset($fichier['size']) || !isset($fichier['type'])) {
            return false;
        }

        // Vérification de la taille
        $tailleMax = 10 * 1024 * 1024; // 10 MB
        if ($fichier['size'] > $tailleMax) {
            $this->journaliserEvenementSecurite('FICHIER_TROP_VOLUMINEUX', 'Tentative de téléchargement d\'un fichier trop volumineux', $fichier);
            return false;
        }

        // Vérification du type MIME
        $typesAutorises = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($fichier['type'], $typesAutorises)) {
            $this->journaliserEvenementSecurite('TYPE_FICHIER_NON_AUTORISE', 'Tentative de téléchargement d\'un type de fichier non autorisé', $fichier);
            return false;
        }

        // Vérification de l'extension
        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensionsAutorisees)) {
            $this->journaliserEvenementSecurite('EXTENSION_NON_AUTORISEE', 'Tentative de téléchargement avec une extension non autorisée', $fichier);
            return false;
        }

        // Vérification de la signature du fichier
        $handle = fopen($fichier['tmp_name'], 'rb');
        if ($handle) {
            $signature = fread($handle, 8);
            fclose($handle);
            
            $signaturesValides = [
                'jpg' => ["\xFF\xD8\xFF"],
                'png' => ["\x89\x50\x4E\x47"],
                'pdf' => ["%PDF"]
            ];
            
            if (isset($signaturesValides[$extension])) {
                $signatureValide = false;
                foreach ($signaturesValides[$extension] as $sig) {
                    if (strpos($signature, $sig) === 0) {
                        $signatureValide = true;
                        break;
                    }
                }
                if (!$signatureValide) {
                    $this->journaliserEvenementSecurite('SIGNATURE_FICHIER_INVALIDE', 'Signature de fichier non conforme', $fichier);
                    return false;
                }
            }
        }

        return true;
    }

    public function nettoyerEntree(string $entree, string $type = 'html'): string
    {
        switch ($type) {
            case 'html':
                return htmlspecialchars(strip_tags(trim($entree)), ENT_QUOTES, 'UTF-8');
            case 'sql':
                return str_replace(['\'', '"', ';', '--', '/*', '*/', 'xp_', 'sp_'], '', $entree);
            case 'xss':
                $entree = preg_replace('/javascript:/i', '', $entree);
                $entree = preg_replace('/on\w+=/i', '', $entree);
                $entree = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $entree);
                return htmlspecialchars($entree, ENT_QUOTES, 'UTF-8');
            default:
                return htmlspecialchars(trim($entree), ENT_QUOTES, 'UTF-8');
        }
    }

    public function journaliserEvenementSecurite(string $evenement, string $description, array $contexte = []): void
    {
        $this->logger->log('warning', "Événement de sécurité: {$evenement} - {$description}", $contexte);
        
        $this->supervisionService->enregistrerAction(
            $_SESSION['numero_utilisateur'] ?? 'SYSTEM',
            $evenement,
            $description,
            null,
            null,
            $contexte
        );
    }

    public function verifierIpAutorisee(string $ip): bool
    {
        // Liste des IPs autorisées (à configurer selon les besoins)
        $ipsAutorisees = [
            '127.0.0.1',
            '::1',
            // Ajouter d'autres IPs selon les besoins
        ];

        // Vérification dans la configuration système
        $stmt = $this->db->prepare("SELECT valeur_parametre FROM parametre_systeme WHERE code_parametre = 'IPS_AUTORISEES'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && !empty($result['valeur_parametre'])) {
            $ipsConfig = explode(',', $result['valeur_parametre']);
            $ipsAutorisees = array_merge($ipsAutorisees, array_map('trim', $ipsConfig));
        }

        return in_array($ip, $ipsAutorisees);
    }

    public function appliquerLimiteDebit(string $identifiant, int $limite, int $periode): bool
    {
        $maintenant = time();
        $fenetre = $maintenant - $periode;

        try {
            // Nettoyer les anciens enregistrements
            $stmt = $this->db->prepare("DELETE FROM rate_limits WHERE timestamp < ?");
            $stmt->execute([$fenetre]);

            // Compter les requêtes dans la période
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM rate_limits WHERE identifier = ? AND timestamp >= ?");
            $stmt->execute([$identifiant, $fenetre]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] >= $limite) {
                $this->journaliserEvenementSecurite('LIMITE_DEBIT_DEPASSEE', "Limite de débit dépassée pour {$identifiant}", [
                    'identifier' => $identifiant,
                    'limit' => $limite,
                    'period' => $periode,
                    'count' => $result['count']
                ]);
                return false;
            }

            // Enregistrer cette requête
            $stmt = $this->db->prepare("INSERT INTO rate_limits (identifier, timestamp) VALUES (?, ?)");
            $stmt->execute([$identifiant, $maintenant]);

            return true;

        } catch (\Exception $e) {
            $this->logger->log('error', 'Erreur lors de l\'application de la limite de débit', [
                'error' => $e->getMessage(),
                'identifier' => $identifiant
            ]);
            // En cas d'erreur, on autorise par défaut
            return true;
        }
    }

    private function getEncryptionKey(): string
    {
        $key = getenv('ENCRYPTION_KEY');
        if (!$key) {
            throw new OperationImpossibleException('Clé de cryptage non configurée.');
        }
        return $key;
    }
}