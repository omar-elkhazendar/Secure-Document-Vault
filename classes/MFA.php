<?php
require_once __DIR__ . '/Database.php';

class MFA {
    private $conn;
    private $table_name = "mfa_secrets";
    private $ga;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // Initialize Google Authenticator
        require_once __DIR__ . '/../vendor/GoogleAuthenticator.php';
        $this->ga = new PHPGangsta_GoogleAuthenticator();
    }

    public function createSecret($userId) {
        // Generate a new secret key
        $secret = $this->ga->createSecret();
        
        // Generate backup codes
        $backupCodes = $this->generateBackupCodes();
        
        // Encode backup codes for storage
        $encodedBackupCodes = json_encode($backupCodes);
        
        // Store in database
        $query = "INSERT INTO " . $this->table_name . "
                (user_id, secret_key, backup_codes)
                VALUES
                (:user_id, :secret_key, :backup_codes)";

        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":secret_key", $secret);
        $stmt->bindParam(":backup_codes", $encodedBackupCodes);

        if($stmt->execute()) {
            return [
                'secret' => $secret,
                'backup_codes' => $backupCodes
            ];
        }
        return false;
    }

    public function getQRCodeUrl($secret, $username) {
        return $this->ga->getQRCodeGoogleUrl($username, $secret);
    }

    public function verifyCode($userId, $code) {
        try {
            // Get user's secret key
            $query = "SELECT secret_key FROM " . $this->table_name . "
                    WHERE user_id = :user_id
                    LIMIT 0,1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $secret = $row['secret_key'];
                
                // Debug log
                error_log("Verifying code with secret: " . $secret);
                error_log("Code to verify: " . $code);
                
                // Verify the code with a larger time window
                $result = $this->ga->verifyCode($secret, $code, 3); // Increased time window to 3
                error_log("Verification result: " . ($result ? 'true' : 'false'));
                
                return $result;
            }
            error_log("No secret found for user ID: " . $userId);
            return false;
        } catch (Exception $e) {
            error_log("Error in verifyCode: " . $e->getMessage());
            return false;
        }
    }

    public function verifyBackupCode($userId, $code) {
        $query = "SELECT backup_codes FROM " . $this->table_name . "
                WHERE user_id = :user_id
                LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $backupCodes = json_decode($row['backup_codes'], true);
            
            // Check if code exists and remove it if found
            if(in_array($code, $backupCodes)) {
                $backupCodes = array_diff($backupCodes, [$code]);
                
                // Update backup codes
                $updateQuery = "UPDATE " . $this->table_name . "
                        SET backup_codes = :backup_codes
                        WHERE user_id = :user_id";
                
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bindParam(":backup_codes", json_encode($backupCodes));
                $updateStmt->bindParam(":user_id", $userId);
                $updateStmt->execute();
                
                return true;
            }
        }
        return false;
    }

    private function generateBackupCodes() {
        $codes = [];
        for($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        }
        return $codes;
    }

    private function updateUserMFAStatus($userId, $enabled) {
        $query = "UPDATE users SET mfa_enabled = :enabled WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":enabled", $enabled);
        $stmt->bindParam(":user_id", $userId);
        return $stmt->execute();
    }
}
?> 