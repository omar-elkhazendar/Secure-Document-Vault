<?php
require_once 'User.php';

class GithubAuth {
    // TODO: Replace these with your actual GitHub OAuth credentials
    // Get these from: GitHub.com → Settings → Developer Settings → OAuth Apps
    private $client_id = "Ov23li2yAWHcJDlT9JBw";        // Replace with the Client ID from your GitHub OAuth App
    private $client_secret = "e59d4cacbce759074f5bd6a8703919a0650ac6c0"; // Replace with the Client Secret from your GitHub OAuth App
    private $redirect_uri = "http://localhost/Data2/github-callback.php";
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function getAuthUrl() {
        $params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'scope' => 'user:email',
            'state' => bin2hex(random_bytes(16)),
            'prompt' => 'consent'  // This will force GitHub to always show the authorization screen
        );
        return 'https://github.com/login/oauth/authorize?' . http_build_query($params);
    }

    public function handleCallback($code) {
        $token = $this->getAccessToken($code);
        if (!$token) {
            return false;
        }

        $userData = $this->getUserData($token);
        if (!$userData) {
            return false;
        }

        // Get user's email
        $userEmail = $this->getUserEmail($token);
        if (!$userEmail) {
            return false;
        }

        // Check if user exists
        $this->user->email = $userEmail;
        if (!$this->user->emailExists()) {
            // Create new user
            $this->user->username = $userData['login'];
            $this->user->auth_method = 'github';
            $this->user->github_id = $userData['id'];
            // For GitHub users, set a random password since they won't use it
            $this->user->password = bin2hex(random_bytes(16));
            if (!$this->user->createFromGitHub($userData['id'])) {
                return false;
            }
        } else {
            // Update existing user's GitHub ID
            $this->user->updateFromGitHub($userData['id']);
        }

        return $this->user;
    }

    private function getAccessToken($code) {
        $params = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'code' => $code,
            'redirect_uri' => $this->redirect_uri
        );

        $ch = curl_init('https://github.com/login/oauth/access_token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log("GitHub OAuth Error: " . curl_error($ch));
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if (!isset($data['access_token'])) {
            error_log("GitHub OAuth Response: " . $response);
        }
        return isset($data['access_token']) ? $data['access_token'] : null;
    }

    private function getUserData($token) {
        $ch = curl_init('https://api.github.com/user');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'User-Agent: PHP-App',
            'Accept: application/vnd.github.v3+json'
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    private function getUserEmail($token) {
        $ch = curl_init('https://api.github.com/user/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'User-Agent: PHP-App',
            'Accept: application/vnd.github.v3+json'
        ));

        $response = curl_exec($ch);
        $emails = json_decode($response, true);
        curl_close($ch);

        // Get the primary email
        if (is_array($emails)) {
            foreach ($emails as $email) {
                if ($email['primary'] && $email['verified']) {
                    return $email['email'];
                }
            }
            // If no primary email found, return the first verified email
            foreach ($emails as $email) {
                if ($email['verified']) {
                    return $email['email'];
                }
            }
        }

        return null;
    }
}
?> 