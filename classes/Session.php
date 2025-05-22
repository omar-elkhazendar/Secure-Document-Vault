<?php
require_once 'DatabaseSession.php';

class Session {
    private $db_session;
    private $db;
    private $session_id;
    private $user_id;
    private $is_logged_in = false;
    private $last_activity;
    private $session_data = [];

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            // Set secure session parameters
            $lifetime = 24 * 60 * 60; // 24 hours by default
            ini_set('session.gc_maxlifetime', $lifetime);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Secure in HTTPS
            session_start();
        }
        $this->db_session = new DatabaseSession();
        
        // Clean up expired sessions
        $this->db_session->cleanup();

        $this->db = new Database();
        $this->check_session();
        $this->check_login();
        $this->update_activity();
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public function destroy() {
        // Delete session from database first
        $this->db_session->destroy();
        
        // Clear all session variables
        $_SESSION = array();
        
        // Destroy the session cookie
        if (isset($_COOKIE[session_name()])) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        
        // Destroy the session
        session_destroy();
        
        // Clear all cookies
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time()-3600, '/');
                setcookie($name, '', time()-3600, '/', '', true, true);
            }
        }
        
        // Prevent caching
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    }

    public function isLoggedIn() {
        // First check if session variables exist
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Then validate session in database
        if (!$this->db_session->validate()) {
            // If session is invalid, clear everything
            $this->destroy();
            return false;
        }
        
        return true;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            // Clear any existing session data
            $this->destroy();
            header("Location: login.php");
            exit();
        }
    }

    public function preventBackNavigation() {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    public function createSession($user_id, $remember = false) {
        // Create session in database with remember me option
        if ($this->db_session->create($user_id, $remember)) {
            $this->set('user_id', $user_id);
            return true;
        }
        return false;
    }

    public function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    private function check_session() {
        // Get session ID
        $this->session_id = session_id();
        
        // Check if session exists in database
        if ($this->db_session->exists($this->session_id)) {
            // Get session data
            $session_data = $this->db_session->get($this->session_id);
            if ($session_data) {
                $this->user_id = $session_data['user_id'];
                $this->last_activity = $session_data['last_activity'];
                $this->session_data = $session_data;
            }
        }
    }

    private function check_login() {
        if (isset($_SESSION['user_id'])) {
            $this->user_id = $_SESSION['user_id'];
            $this->is_logged_in = true;
        }
    }

    private function update_activity() {
        if ($this->session_id) {
            $this->db_session->update_activity($this->session_id);
        }
    }
}
?> 