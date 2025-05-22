<?php
require_once __DIR__ . '/Database.php';
require_once 'User.php';
require_once 'CryptoHandler.php';
require_once 'Role.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/hmac_config.php';

class Document {
    private $db;
    private $user;
    private $upload_dir;
    private $hmacSecretKey;
    private $base_path;
    private $userId;
    private $role;

    public function __construct($db, $userId) {
        $this->db = $db;
        $this->userId = $userId;
        $this->role = new Role($db, $userId);
        $this->base_path = dirname(__DIR__);
        $this->upload_dir = UPLOAD_DIR;
        
        // Set base path to project root
        $this->base_path = dirname(__DIR__);
        
        // Set upload directory from config
        $this->upload_dir = UPLOAD_DIR;
        
        // Create upload directory if it doesn't exist
        $upload_path = $this->base_path . DIRECTORY_SEPARATOR . $this->upload_dir;
        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        
        // Use the HMAC secret key from config
        $this->hmacSecretKey = HMAC_SECRET_KEY;
    }

    private function getAbsolutePath($relativePath) {
        return $this->base_path . DIRECTORY_SEPARATOR . $relativePath;
    }

    private function validateFile($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed');
        }

        if ($file['size'] > UPLOAD_MAX_SIZE) {
            throw new Exception('File size exceeds maximum limit of ' . (UPLOAD_MAX_SIZE / 1024 / 1024) . 'MB');
        }

        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, ALLOWED_FILE_TYPES)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', ALLOWED_FILE_TYPES));
        }

        return true;
    }

    public function uploadDocument($title, $description, $file, $user_id, $share_with_all = false) {
        // Get PDO connection if Database object is passed
        $db = ($this->db instanceof Database) ? $this->db->getConnection() : $this->db;

        // Validate file
        $this->validateFile($file);

        // Read file content
        $fileContent = file_get_contents($file['tmp_name']);
        if ($fileContent === false) {
            throw new Exception('Failed to read uploaded file content.');
        }

        // Initialize crypto handler
        $cryptoHandler = new CryptoHandler($db, $user_id);

        // Generate SHA-256 hash
        $sha256Hash = hash('sha256', $fileContent);

        // Generate digital signature
        $privateKeyResource = $cryptoHandler->getPrivateKeyResource();
        if (!$privateKeyResource) {
            throw new Exception('User private key not found or could not be loaded for signing.');
        }

        $digitalSignature = '';
        if (!openssl_sign($sha256Hash, $digitalSignature, $privateKeyResource, OPENSSL_ALGO_SHA256)) {
            throw new Exception('Failed to generate digital signature.');
        }
        $digitalSignature = base64_encode($digitalSignature);

        // Generate HMAC signature
        $hmacSignature = hash_hmac('sha256', $fileContent, $this->hmacSecretKey);

        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $relative_path = $this->upload_dir . $filename;
        $absolute_path = $this->getAbsolutePath($relative_path);

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $absolute_path)) {
            throw new Exception('Failed to move uploaded file.');
        }

        try {
            $db->beginTransaction();

            // Insert document record
            $sql = "INSERT INTO documents (title, description, file_path, file_type, file_size, uploaded_by, sha256_hash, hmac_signature, digital_signature) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $title,
                $description,
                $relative_path,
                $file['type'],
                $file['size'],
                $user_id,
                $sha256Hash,
                $hmacSignature,
                $digitalSignature
            ]);

            $document_id = $db->lastInsertId();

            if ($share_with_all) {
                $this->shareWithAllUsers($document_id);
            }

            $db->commit();
            $this->logAction($user_id, 'upload', "Uploaded and signed document: $title");
            return $document_id;
        } catch (Exception $e) {
            $db->rollBack();
            // Clean up uploaded file if database insert fails
            if (file_exists($absolute_path)) {
                unlink($absolute_path);
            }
            throw $e;
        }
    }

    public function deleteDocument($documentId, $userId) {
        // Get PDO connection if Database object is passed
        $db = ($this->db instanceof Database) ? $this->db->getConnection() : $this->db;

        // Allow uploader or admin to delete
        $sql = "SELECT * FROM documents WHERE document_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$documentId]);
        $document = $stmt->fetch();

        if (!$document) {
            throw new Exception('Document not found');
        }

        // Check if user is uploader or admin
        $isUploader = ($document['uploaded_by'] == $userId);
        $isAdmin = $this->role->isAdmin($userId);

        if (!$isUploader && !$isAdmin) {
            throw new Exception('You do not have permission to delete this document');
        }

        try {
            $db->beginTransaction();

            // Delete document signatures first
            $sql = "DELETE FROM document_signatures WHERE document_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$documentId]);

            // Delete document shares
            $sql = "DELETE FROM document_shares WHERE document_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$documentId]);

            // Delete document versions
            $sql = "DELETE FROM document_versions WHERE document_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$documentId]);

            // Delete the physical file
            $filePath = $this->getAbsolutePath($document['file_path']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Finally, delete the document
            $sql = "DELETE FROM documents WHERE document_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$documentId]);

            $db->commit();
            $this->logAction($userId, 'delete', "Deleted document: {$document['title']}");
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private function logAction($userId, $action, $details) {
        // Get PDO connection if Database object is passed
        $db = ($this->db instanceof Database) ? $this->db->getConnection() : $this->db;
        
        $sql = "INSERT INTO system_logs (user_id, action, details) VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $action, $details]);
    }

    private function shareWithAllUsers($documentId) {
        $sql = "INSERT INTO document_shares (document_id, user_id, permission_level) 
                SELECT ?, id, 'read' FROM users WHERE id != ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$documentId, $_SESSION['user_id']]);
    }

    /**
     * Sign a document with a user's signature
     * @param int $documentId The ID of the document to sign
     * @param int $userId The ID of the user signing the document
     * @param string $signatureData The signature data (base64 encoded image)
     * @return bool True if signing was successful
     * @throws Exception If signing fails
     */
    public function signDocument($documentId, $userId, $signatureData) {
        try {
            // Get PDO connection
            $db = $this->db instanceof PDO ? $this->db : $this->db->getConnection();
            
            // Start transaction
            $db->beginTransaction();
            
            // Check if user has permission to sign the document
            $stmt = $db->prepare("
                SELECT d.*, ds.user_id as shared_with 
                FROM documents d 
                LEFT JOIN document_shares ds ON d.document_id = ds.document_id 
                WHERE d.document_id = ? 
                AND (d.uploaded_by = ? OR ds.user_id = ?)
            ");
            $stmt->execute([$documentId, $userId, $userId]);
            $document = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$document) {
                throw new Exception("Document not found or you don't have permission to sign it.");
            }
            
            // Check if document is already signed
            $stmt = $db->prepare("
                SELECT COUNT(*) as signature_count 
                FROM document_signatures 
                WHERE document_id = ? AND user_id = ?
            ");
            $stmt->execute([$documentId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['signature_count'] > 0) {
                throw new Exception("You have already signed this document.");
            }
            
            // Store the signature
            $stmt = $db->prepare("
                INSERT INTO document_signatures (
                    document_id, 
                    user_id, 
                    signature_data, 
                    signed_at
                ) VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$documentId, $userId, $signatureData]);
            
            // Log the signing action
            $this->logAction($userId, 'sign', "Signed document ID: " . $documentId);
            
            // Commit transaction
            $db->commit();
            
            return true;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw new Exception("Failed to sign document: " . $e->getMessage());
        }
    }
} 