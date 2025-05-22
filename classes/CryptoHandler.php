<?php

class CryptoHandler {
    private $db;
    private $userId;
    private $privateKey;
    private $publicKey;

    public function __construct($db, $userId) {
        // Get PDO connection if Database object is passed
        $this->db = ($db instanceof Database) ? $db->getConnection() : $db;
        $this->userId = $userId;
        $this->loadKeys();
    }

    private function loadKeys() {
        $stmt = $this->db->prepare("
            SELECT private_key_encrypted, public_key 
            FROM user_keys 
            WHERE user_id = ?
        ");
        $stmt->execute([$this->userId]);
        $keys = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($keys) {
            $this->privateKey = $this->decryptPrivateKey($keys['private_key_encrypted']);
            $this->publicKey = $keys['public_key'];
        } else {
            // Generate new key pair if none exists
            $this->generateNewKeyPair();
        }
    }

    private function generateNewKeyPair() {
        // Generate new RSA key pair
        $config = array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            "config" => "C:\\xamppp\\apache\\conf\\openssl.cnf" // Updated path to match your XAMPP installation
        );
        
        // Check if OpenSSL extension is loaded
        if (!extension_loaded('openssl')) {
            throw new Exception("OpenSSL extension is not loaded. Please enable it in your PHP configuration.");
        }

        // Check if config file exists
        if (!file_exists($config['config'])) {
            // Try alternative paths
            $alternative_paths = [
                "C:\\xamppp\\apache\\conf\\openssl.cnf",
                "C:\\xamppp\\php\\extras\\ssl\\openssl.cnf",
                "C:\\xamppp\\php\\lib\\openssl.cnf",
                "C:\\xamppp\\php\\openssl.cnf"
            ];

            $config_found = false;
            foreach ($alternative_paths as $path) {
                if (file_exists($path)) {
                    $config['config'] = $path;
                    $config_found = true;
                    break;
                }
            }

            if (!$config_found) {
                // If no config file found, try without config
                unset($config['config']);
            }
        }
        
        $res = openssl_pkey_new($config);

        if ($res === false) {
            $errorMessage = "OpenSSL key generation failed. Check OpenSSL extension and configuration.";
            while ($msg = openssl_error_string()) {
                $errorMessage .= " OpenSSL Error: " . $msg;
            }
            error_log($errorMessage);
            throw new Exception("Failed to generate new key pair. " . $errorMessage);
        }
        
        // Get private key
        if (!openssl_pkey_export($res, $privateKey, null, $config)) {
            $errorMessage = "Failed to export private key.";
            while ($msg = openssl_error_string()) {
                $errorMessage .= " OpenSSL Error: " . $msg;
            }
            throw new Exception($errorMessage);
        }
        
        // Get public key
        $details = openssl_pkey_get_details($res);
        if ($details === false) {
            $errorMessage = "Failed to get public key details.";
            while ($msg = openssl_error_string()) {
                $errorMessage .= " OpenSSL Error: " . $msg;
            }
            throw new Exception($errorMessage);
        }
        
        $publicKey = $details['key'];
        
        // Store keys in database
        try {
            $stmt = $this->db->prepare("INSERT INTO user_keys (user_id, public_key, private_key_encrypted) VALUES (?, ?, ?)");
            $encryptedPrivateKey = $this->encryptPrivateKey($privateKey);
            $stmt->execute([$this->userId, $publicKey, $encryptedPrivateKey]);
            
            $this->publicKey = $publicKey;
            $this->privateKey = $privateKey;
        } catch (Exception $e) {
            throw new Exception("Failed to store keys in database: " . $e->getMessage());
        }
    }

    private function encryptPrivateKey($privateKey) {
        // Implement private key encryption using a master key or user's password
        // This is a placeholder - implement proper encryption
        // For example, using AES encryption with a key derived from the user's password
        // This method should return the encrypted private key.

        // *** IMPORTANT: Replace this with actual strong encryption ***
        return base64_encode($privateKey); // Placeholder: base64 encoding is NOT encryption
    }

    private function decryptPrivateKey($encryptedPrivateKey) {
        // Implement private key decryption
        // This is a placeholder - implement proper decryption
        // This method should return the decrypted private key in a format usable by openssl functions.

        // *** IMPORTANT: Replace this with actual strong decryption ***
        return base64_decode($encryptedPrivateKey); // Placeholder: base64 decoding is NOT decryption
    }

    public function encryptFile($fileData) {
        // Generate random IV for AES-256-CBC
        $iv = openssl_random_pseudo_bytes(16);
        
        // Generate random AES key
        $aesKey = openssl_random_pseudo_bytes(32);
        
        // Encrypt file with AES-256-CBC
        $encryptedData = openssl_encrypt(
            $fileData,
            'AES-256-CBC',
            $aesKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        // Encrypt AES key with RSA public key
        $encryptedKey = '';
        if (!openssl_public_encrypt($aesKey, $encryptedKey, $this->publicKey)) {
            throw new Exception('Failed to encrypt AES key with public key.');
        }
        
        // Generate SHA-256 hash of original file
        $fileHash = hash('sha256', $fileData);
        
        // Sign the hash with private key
        $signature = '';
        // IMPORTANT: Ensure $this->privateKey is a valid private key resource here
        if (!openssl_sign($fileHash, $signature, $this->privateKey, OPENSSL_ALGO_SHA256)) {
            throw new Exception('Failed to generate digital signature.');
        }
        
        return [
            'encrypted_data' => $encryptedData,
            'encrypted_key' => base64_encode($encryptedKey),
            'iv' => base64_encode($iv),
            'file_hash' => $fileHash,
            'signature' => base64_encode($signature)
        ];
    }

    public function decryptFile($encryptedData, $encryptedKey, $iv) {
        // Decrypt AES key with private key
        $aesKey = '';
         // IMPORTANT: Ensure $this->privateKey is a valid private key resource here
        if (!openssl_private_decrypt(
            base64_decode($encryptedKey),
            $aesKey,
            $this->privateKey
        )) {
             throw new Exception('Failed to decrypt AES key with private key.');
        }
        
        // Decrypt file with AES-256-CBC
        $decryptedData = openssl_decrypt(
            $encryptedData,
            'AES-256-CBC',
            $aesKey,
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        );
        
        return $decryptedData;
    }

    public function verifySignature($fileData, $signature, $publicKey) {
        $fileHash = hash('sha256', $fileData);
        // IMPORTANT: Ensure $publicKey is a valid public key resource here
        return openssl_verify(
            $fileHash,
            base64_decode($signature),
            $publicKey,
            OPENSSL_ALGO_SHA256
        ) === 1;
    }

    public function getPublicKey() {
        return $this->publicKey;
    }

    // Added method to get the private key resource for signing outside the class
    // IMPORTANT: Ensure $this->privateKey is a valid private key resource before returning
    public function getPrivateKeyResource() {
        return openssl_pkey_get_private($this->privateKey);
    }
} 