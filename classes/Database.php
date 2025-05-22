<?php
if (!class_exists('Database')) {
    class Database {
        private $host = 'localhost';
        private $db_name = 'auth_system';
        private $username = 'root';
        private $password = '';
        private $conn;

        public function __construct() {
            try {
                $this->conn = new PDO(
                    "mysql:host={$this->host};dbname={$this->db_name}",
                    $this->username,
                    $this->password
                );
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                throw new Exception("Connection failed: " . $e->getMessage());
            }
        }

        public function getConnection() {
            return $this->conn;
        }

        public function query($sql, $params = []) {
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute($params);
                return $stmt;
            } catch(PDOException $e) {
                throw new Exception("Query failed: " . $e->getMessage());
            }
        }

        public function lastInsertId() {
            return $this->conn->lastInsertId();
        }
    }
} 