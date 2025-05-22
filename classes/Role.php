<?php
require_once __DIR__ . '/Database.php';

class Role {
    private $conn;
    private $userId;
    private $roleId;
    private $roleName;

    public function __construct($db, $userId = null) {
        if ($db instanceof Database) {
            $this->conn = $db->getConnection();
        } else {
            $this->conn = $db;
        }
        $this->userId = $userId;
        if ($userId !== null) {
            $this->loadRole();
        }
    }

    private function loadRole() {
        if ($this->userId === null) {
            return;
        }
        
        $stmt = $this->conn->prepare("
            SELECT r.role_id, r.role_name 
            FROM roles r 
            JOIN users u ON r.role_id = u.role_id 
            WHERE u.id = ?
        ");
        $stmt->execute([$this->userId]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($role) {
            $this->roleId = $role['role_id'];
            $this->roleName = $role['role_name'];
        }
    }

    public function isAdmin($userId = null) {
        if ($userId !== null) {
            $stmt = $this->conn->prepare("
                SELECT r.role_name 
                FROM roles r 
                JOIN users u ON r.role_id = u.role_id 
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $role = $stmt->fetch(PDO::FETCH_ASSOC);
            return $role && $role['role_name'] === 'admin';
        }
        return $this->roleName === 'admin';
    }

    public function getRoleName() {
        return $this->roleName;
    }

    public function getRoleId() {
        return $this->roleId;
    }

    public function assignRoleToUser($user_id, $role_id) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET role_id = ? WHERE id = ?");
            return $stmt->execute([$role_id, $user_id]);
        } catch (PDOException $e) {
            error_log("Error assigning role to user: " . $e->getMessage());
            return false;
        }
    }

    public function getUserRole($user_id) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT r.* 
                 FROM roles r 
                 JOIN users u ON r.role_id = u.role_id 
                 WHERE u.id = ?"
            );
            $stmt->execute([$user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user role: " . $e->getMessage());
            return false;
        }
    }

    public function getUserPermissions($user_id) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT p.* 
                 FROM permissions p 
                 JOIN role_permissions rp ON p.permission_id = rp.permission_id 
                 JOIN users u ON rp.role_id = u.role_id 
                 WHERE u.id = ?"
            );
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user permissions: " . $e->getMessage());
            return [];
        }
    }

    public function hasPermission($user_id, $permission_name) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT p.* 
                 FROM permissions p 
                 JOIN role_permissions rp ON p.permission_id = rp.permission_id 
                 JOIN users u ON rp.role_id = u.role_id 
                 WHERE u.id = ? AND p.permission_name = ?"
            );
            $stmt->execute([$user_id, $permission_name]);
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $e) {
            error_log("Error checking permission: " . $e->getMessage());
            return false;
        }
    }

    public function createRole($role_name) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO roles (role_name) VALUES (?)");
            return $stmt->execute([$role_name]);
        } catch (PDOException $e) {
            error_log("Error creating role: " . $e->getMessage());
            return false;
        }
    }

    public function assignPermissionToRole($role_id, $permission_id) {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)"
            );
            return $stmt->execute([$role_id, $permission_id]);
        } catch (PDOException $e) {
            error_log("Error assigning permission to role: " . $e->getMessage());
            return false;
        }
    }

    public function removePermissionFromRole($role_id, $permission_id) {
        try {
            $stmt = $this->conn->prepare(
                "DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?"
            );
            return $stmt->execute([$role_id, $permission_id]);
        } catch (PDOException $e) {
            error_log("Error removing permission from role: " . $e->getMessage());
            return false;
        }
    }

    public function getAllRoles() {
        try {
            $stmt = $this->conn->query("SELECT * FROM roles");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all roles: " . $e->getMessage());
            return [];
        }
    }

    public function getAllPermissions() {
        try {
            $stmt = $this->conn->query("SELECT * FROM permissions");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all permissions: " . $e->getMessage());
            return [];
        }
    }

    public function getRolePermissions($role_id) {
        try {
            $stmt = $this->conn->prepare(
                "SELECT p.* 
                 FROM permissions p 
                 JOIN role_permissions rp ON p.permission_id = rp.permission_id 
                 WHERE rp.role_id = ?"
            );
            $stmt->execute([$role_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting role permissions: " . $e->getMessage());
            return [];
        }
    }

    public function deleteRole($role_id) {
        try {
            // Check if any users are assigned to this role
            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) as count FROM users WHERE role_id = ?"
            );
            $stmt->execute([$role_id]);
            $users = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($users['count'] > 0) {
                throw new Exception('Cannot delete role: Users are still assigned to this role');
            }

            $stmt = $this->conn->prepare("DELETE FROM roles WHERE role_id = ?");
            return $stmt->execute([$role_id]);

        } catch (PDOException $e) {
            error_log("Error deleting role: " . $e->getMessage());
            return false;
        }
    }

    public function getRoleIdByName($role_name) {
        try {
            $stmt = $this->conn->prepare("SELECT role_id FROM roles WHERE role_name = ? LIMIT 1");
            $stmt->execute([$role_name]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['role_id'] : null;
        } catch (PDOException $e) {
            error_log("Error getting role ID: " . $e->getMessage());
            return null;
        }
    }
} 