<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/IpHelper.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $auth_method;
    public $github_id;
    public $created_at;
    public $status;
    public $mfa_enabled;
    public $updated_at;
    public $okta_id;
    public $google_id;
    public $profile_picture;
    public $role_id;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /* ====================== */
    /* CORE USER METHODS      */
    /* ====================== */

    public function create() {
        // Validate required fields
        if (empty($this->email) || empty($this->username)) {
            throw new InvalidArgumentException('Email and username are required');
        }

        // Only hash password if provided
        $passwordHash = !empty($this->password) 
            ? password_hash($this->password, PASSWORD_BCRYPT) 
            : null;

        $query = "INSERT INTO " . $this->table_name . "
                SET username = :username,
                    email = :email,
                    password = :password,
                    auth_method = :auth_method,
                    status = :status,
                    google_id = :google_id,
                    profile_picture = :profile_picture,
                    created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        $this->auth_method = $this->auth_method ?? 'local';
        $this->status = $this->status ?? 'pending';
        $this->google_id = $this->google_id ?? null;
        $this->profile_picture = $this->profile_picture ?? null;

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $passwordHash);
        $stmt->bindParam(":auth_method", $this->auth_method);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":google_id", $this->google_id);
        $stmt->bindParam(":profile_picture", $this->profile_picture);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        error_log("User creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    public function emailExists() {
        $query = "SELECT id, username, password, auth_method, status
                FROM " . $this->table_name . "
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password = $row['password'];
            $this->auth_method = $row['auth_method'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    public function usernameExists() {
        $query = "SELECT id
                FROM " . $this->table_name . "
                WHERE username = :username
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /* ====================== */
    /* OKTA INTEGRATION       */
    /* ====================== */

    public function createFromOkta($okta_id) {
        if (empty($this->email)) {
            throw new InvalidArgumentException('Email is required for Okta registration');
        }

        $this->username = $this->username ?? $this->generateUsername($this->email);

        $query = "INSERT INTO " . $this->table_name . "
                SET username = :username,
                    email = :email,
                    auth_method = :auth_method,
                    okta_id = :okta_id,
                    status = :status,
                    created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        $this->auth_method = 'okta';
        $this->status = 'active';

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":auth_method", $this->auth_method);
        $stmt->bindParam(":okta_id", $okta_id);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        error_log("Okta user creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    public function updateFromOkta($okta_id) {
        if (empty($this->email)) {
            throw new InvalidArgumentException('Email is required for Okta update');
        }

        $query = "UPDATE " . $this->table_name . "
                SET username = :username,
                    okta_id = :okta_id,
                    auth_method = :auth_method,
                    status = 'active',
                    updated_at = NOW()
                WHERE email = :email";

        $stmt = $this->conn->prepare($query);

        $this->auth_method = 'okta';

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":okta_id", $okta_id);
        $stmt->bindParam(":auth_method", $this->auth_method);
        $stmt->bindParam(":email", $this->email);

        if (!$stmt->execute()) {
            error_log("Okta user update failed: " . implode(" ", $stmt->errorInfo()));
            return false;
        }
        return true;
    }

    public function findByOktaId($okta_id) {
        $query = "SELECT id, username, email, auth_method, status, role_id
                FROM " . $this->table_name . "
                WHERE okta_id = :okta_id
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":okta_id", $okta_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->auth_method = $row['auth_method'];
            $this->status = $row['status'];
            $this->role_id = $row['role_id'];
            return true;
        }
        return false;
    }

    /* ====================== */
    /* OTHER AUTH METHODS     */
    /* ====================== */

    public function createFromGitHub($github_id) {
        if (empty($this->email)) {
            throw new InvalidArgumentException('Email is required for GitHub registration');
        }

        $this->username = $this->username ?? $this->generateUsername($this->email);

        $query = "INSERT INTO " . $this->table_name . "
                SET username = :username,
                    email = :email,
                    auth_method = :auth_method,
                    github_id = :github_id,
                    status = :status,
                    created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        $this->auth_method = 'github';
        $this->status = 'active';

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":auth_method", $this->auth_method);
        $stmt->bindParam(":github_id", $github_id);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        error_log("GitHub user creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    public function updateFromGitHub($github_id) {
        if (empty($this->email)) {
            throw new InvalidArgumentException('Email is required for GitHub update');
        }

        $query = "UPDATE " . $this->table_name . "
                SET username = :username,
                    github_id = :github_id,
                    auth_method = :auth_method,
                    status = 'active',
                    updated_at = NOW()
                WHERE email = :email";

        $stmt = $this->conn->prepare($query);

        $this->auth_method = 'github';

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":github_id", $github_id);
        $stmt->bindParam(":auth_method", $this->auth_method);
        $stmt->bindParam(":email", $this->email);

        if (!$stmt->execute()) {
            error_log("GitHub user update failed: " . implode(" ", $stmt->errorInfo()));
            return false;
        }
        return true;
    }

    public function createUser($user_data) {
        if (empty($user_data['email'])) {
            throw new InvalidArgumentException('Email is required');
        }

        $this->username = $user_data['username'] ?? $this->generateUsername($user_data['email']);

        $query = "INSERT INTO " . $this->table_name . "
                SET username = :username,
                    email = :email,
                    google_id = :google_id,
                    auth_method = :auth_method,
                    profile_picture = :profile_picture,
                    status = :status,
                    created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        $auth_method = 'google';
        $status = 'active';

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $user_data['email']);
        $stmt->bindParam(":google_id", $user_data['google_id']);
        $stmt->bindParam(":auth_method", $auth_method);
        $stmt->bindParam(":profile_picture", $user_data['profile_picture']);
        $stmt->bindParam(":status", $status);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        error_log("Google user creation failed: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    /* ====================== */
    /* USER MANAGEMENT        */
    /* ====================== */

    public function updateUser($user_id, $user_data) {
        try {
            $query = "UPDATE users SET 
                    username = :username,
                    email = :email,
                    auth_method = :auth_method,
                    status = :status,
                    google_id = :google_id,
                    profile_picture = :profile_picture,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Extract values from array to variables
        $username = $user_data['name'];
        $email = $user_data['email'];
        $auth_method = $user_data['auth_method'] ?? 'google';
        $status = $user_data['status'] ?? 'active';
        $google_id = $user_data['google_id'];
        $profile_picture = $user_data['profile_picture'];

        // Bind parameters using variables
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":auth_method", $auth_method);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":google_id", $google_id);
        $stmt->bindParam(":profile_picture", $profile_picture);
        $stmt->bindParam(":id", $user_id);

        if (!$stmt->execute()) {
            error_log("User update failed: " . implode(" ", $stmt->errorInfo()));
            return false;
        }
        return true;
        } catch (Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }

    public function activateAccount($user_id) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("UPDATE users SET status = 'active' WHERE id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update user status');
            }

            require_once __DIR__ . '/Role.php';
            $role = new Role($this->conn);
            $user_role_id = $role->getRoleIdByName('user');

            if (!$user_role_id) {
                throw new Exception('User role not found');
            }

            if (!$role->assignRoleToUser($user_id, $user_role_id)) {
                throw new Exception('Failed to assign user role');
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error activating account: " . $e->getMessage());
            return false;
        }
    }

    public function delete($user_id) {
        try {
            $this->conn->beginTransaction();

            // Delete user's documents first
            $stmt = $this->conn->prepare("DELETE FROM documents WHERE uploaded_by = ?");
            $stmt->execute([$user_id]);

            // Delete user's document shares
            $stmt = $this->conn->prepare("DELETE FROM document_shares WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Delete user's document signatures
            $stmt = $this->conn->prepare("DELETE FROM document_signatures WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Delete user's keys
            $stmt = $this->conn->prepare("DELETE FROM user_keys WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Delete user's system logs
            $stmt = $this->conn->prepare("DELETE FROM system_logs WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Delete user's MFA secrets
            $stmt = $this->conn->prepare("DELETE FROM mfa_secrets WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Delete user's login logs
            $stmt = $this->conn->prepare("DELETE FROM login_logs WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Finally, delete the user
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    /* ====================== */
    /* LOGIN/LOGOUT METHODS  */
    /* ====================== */

    public function login($email, $password) {
        try {
            $query = "SELECT id, username, email, password, status, mfa_enabled 
                    FROM " . $this->table_name . " 
                    WHERE email = :email 
                    LIMIT 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($row['status'] === 'pending') {
                    return ['success' => false, 'message' => 'Account pending approval'];
                }

                if ($row['status'] === 'inactive') {
                    return ['success' => false, 'message' => 'Account deactivated'];
                }

                if (password_verify($password, $row['password'])) {
                    $this->id = $row['id'];
                    $this->username = $row['username'];
                    $this->email = $row['email'];
                    $this->status = $row['status'];
                    $this->logLogin(null, true);
                    return ['success' => true, 'mfa_enabled' => $row['mfa_enabled']];
                }
            }

            if (isset($row['id'])) {
                $this->logLogin(null, false);
            }

            return ['success' => false, 'message' => 'Invalid credentials'];
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'System error'];
        }
    }

    public function logLogin($ip_address = null, $success = true) {
        try {
            if ($ip_address !== null) {
                $formattedIp = IpHelper::formatIpForDisplay($ip_address);
                $query = "INSERT INTO login_logs (user_id, ip_address, login_time, auth_method)
                        VALUES (:user_id, :ip_address, NOW(), 
                        (SELECT auth_method FROM users WHERE id = :user_id))";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $this->id);
                $stmt->bindParam(":ip_address", $formattedIp);
            } else {
                $query = "INSERT INTO login_logs 
                        (user_id, ip_address, user_agent, success, created_at) 
                        VALUES 
                        (:user_id, :ip_address, :user_agent, :success, NOW())";
                $stmt = $this->conn->prepare($query);
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $stmt->bindParam(":user_id", $this->id);
                $stmt->bindParam(":ip_address", $ip_address);
                $stmt->bindParam(":user_agent", $user_agent);
                $stmt->bindParam(":success", $success, PDO::PARAM_BOOL);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Login logging failed: " . $e->getMessage());
            return false;
        }
    }

    /* ====================== */
    /* GETTER METHODS         */
    /* ====================== */

    public function getLoginHistory($limit = 10) {
        $query = "SELECT ip_address, login_time, auth_method, success
                FROM login_logs 
                WHERE user_id = :user_id 
                ORDER BY login_time DESC 
                LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAccountInfo() {
        $query = "SELECT username, email, auth_method, created_at, status, role_id
                FROM " . $this->table_name . " 
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByEmail($email) {
        $query = "SELECT id, username, email, auth_method, google_id, status
                FROM " . $this->table_name . "
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt->rowCount() > 0 ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    public function getUserById($id) {
        $query = "SELECT id, username, email, auth_method, status, mfa_enabled, role_id
                FROM " . $this->table_name . "
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->rowCount() > 0 ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    public function findByGithubId($github_id) {
        $query = "SELECT id, username, email, auth_method, status
                FROM " . $this->table_name . "
                WHERE github_id = :github_id
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":github_id", $github_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->auth_method = $row['auth_method'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    /* ====================== */
    /* UTILITY METHODS        */
    /* ====================== */

    private function generateUsername($email) {
        $prefix = strtok($email, '@');
        $cleanPrefix = preg_replace('/[^a-zA-Z0-9]/', '', $prefix);
        $suffix = bin2hex(random_bytes(2));
        return substr($cleanPrefix, 0, 20) . '_' . $suffix;
    }
}
?>