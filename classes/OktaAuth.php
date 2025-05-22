<?php
require_once 'User.php';

class OktaAuth {
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $issuer;
    private $user;

    public function __construct() {
        // TODO: Replace these with your actual Okta credentials
        $this->client_id = "YOUR_OKTA_CLIENT_ID";
        $this->client_secret = "YOUR_OKTA_CLIENT_SECRET";
        $this->redirect_uri = "http://localhost/Data2/okta-callback.php";
        $this->issuer = "https://YOUR_OKTA_DOMAIN/oauth2/default";
        $this->user = new User();
    }

    public function getAuthUrl() {
        $params = array(
            'client_id' => $this->client_id,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'redirect_uri' => $this->redirect_uri,
            'state' => bin2hex(random_bytes(16))
        );
        return $this->issuer . '/v1/authorize?' . http_build_query($params);
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

        // Check if user exists
        $this->user->email = $userData['email'];
        if (!$this->user->emailExists()) {
            // Create new user
            $this->user->username = $userData['preferred_username'] ?? explode('@', $userData['email'])[0];
            $this->user->auth_method = 'okta';
            if (!$this->user->createFromOkta($userData['sub'])) {
                return false;
            }
        } else {
            // Update existing user's Okta ID
            $this->user->updateFromOkta($userData['sub']);
        }

        return $this->user;
    }

    private function getAccessToken($code) {
        $params = array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirect_uri
        );

        $ch = curl_init($this->issuer . '/v1/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log("Okta OAuth Error: " . curl_error($ch));
        }
        curl_close($ch);

        $data = json_decode($response, true);
        return isset($data['access_token']) ? $data['access_token'] : null;
    }

    private function getUserData($token) {
        $ch = curl_init($this->issuer . '/v1/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
} 