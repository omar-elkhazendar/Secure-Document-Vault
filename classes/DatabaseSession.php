<?php
require_once __DIR__ . '/Database.php';

class DatabaseSession {
    private $conn;
    private $table_name = "sessions";
    private $session_id;
    private $user_id;
    private $ip_address;
    private $user_agent;
    private $last_activity;
    private $created_at;
    private $expires_at;
    
    // Default session duration (24 hours)
    const DEFAULT_SESSION_DURATION = '+24 hours';
    // Extended session duration (30 days)
    const EXTENDED_SESSION_DURATION = '+30 days';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->session_id = session_id();
        $this->ip_address = $_SERVER['REMOTE_ADDR'];
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'];
    }

    public function exists($session_id) {
        $query = "SELECT id FROM " . $this->table_name . " 
                WHERE id = :session_id 
                AND expires_at > NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":session_id", $session_id);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function get($session_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                WHERE id = :session_id 
                AND expires_at > NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":session_id", $session_id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function create($user_id, $remember = false) {
        $this->user_id = $user_id;
        $this->last_activity = date('Y-m-d H:i:s');
        $this->created_at = date('Y-m-d H:i:s');

        // Set expiration time based on remember me
        $expires = $remember ?
            date('Y-m-d H:i:s', strtotime('+30 days')) :
            date('Y-m-d H:i:s', strtotime('+24 hours'));

        // First, check if a session with this ID already exists
        $existing_session_query = "SELECT id FROM " . $this->table_name . " WHERE id = :session_id LIMIT 1";
        $existing_session_stmt = $this->conn->prepare($existing_session_query);
        $existing_session_stmt->bindParam(":session_id", $this->session_id);
        $existing_session_stmt->execute();

        if ($existing_session_stmt->rowCount() > 0) {
            // If session exists, update it
            $query = "UPDATE " . $this->table_name . "
                    SET user_id = :user_id,
                        ip_address = :ip_address,
                        user_agent = :user_agent,
                        last_activity = :last_activity,
                        expires_at = :expires_at
                    WHERE id = :session_id";

            $stmt = $this->conn->prepare($query);
        } else {
            // If session does not exist, insert a new one
            $query = "INSERT INTO " . $this->table_name . "
                    (id, user_id, ip_address, user_agent, last_activity, created_at, expires_at)
                    VALUES
                    (:session_id, :user_id, :ip_address, :user_agent, :last_activity, :created_at, :expires_at)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":created_at", $this->created_at);
        }

        $stmt->bindParam(":session_id", $this->session_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":ip_address", $this->ip_address);
        $stmt->bindParam(":user_agent", $this->user_agent);
        $stmt->bindParam(":last_activity", $this->last_activity);
        $stmt->bindParam(":expires_at", $expires);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error creating/updating session: " . $e->getMessage());
            return false;
        }
    }

    public function update_activity($session_id) {
        $this->last_activity = date('Y-m-d H:i:s');
        
        $query = "UPDATE " . $this->table_name . "
                SET last_activity = :last_activity
                WHERE id = :session_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":last_activity", $this->last_activity);
        $stmt->bindParam(":session_id", $session_id);
        
        return $stmt->execute();
    }

    public function destroy() {
        if($this->session_id) {
            $query = "DELETE FROM " . $this->table_name . " 
                    WHERE id = :session_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":session_id", $this->session_id);
            
            return $stmt->execute();
        }
        return false;
    }

    public function validate() {
        if(!$this->session_id) {
            return false;
        }
        
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE id = :session_id 
                AND ip_address = :ip_address 
                AND user_agent = :user_agent 
                AND expires_at > NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":session_id", $this->session_id);
        $stmt->bindParam(":ip_address", $this->ip_address);
        $stmt->bindParam(":user_agent", $this->user_agent);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function cleanup() {
        $query = "DELETE FROM " . $this->table_name . " 
                WHERE expires_at <= NOW()";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute();
    }
}
?> 