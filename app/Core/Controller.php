<?php
/**
 * Classe de base pour tous les contrôleurs
 */

class Controller {
    protected $db;
    protected $viewPath = '../app/Views/';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->startSession();
    }

    protected function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            session_start();
        }
        
        // Vérifier l'expiration de session
        $this->checkSessionExpiry();
    }

    protected function checkSessionExpiry() {
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
                $this->logout();
                return;
            }
        }
        $_SESSION['last_activity'] = time();
    }

    protected function view($view, $data = []) {
        // Charger le helper d'URL
        if (!function_exists('url')) {
            require_once '../app/Helpers/UrlHelper.php';
        }
        
        extract($data);
        require_once $this->viewPath . 'layouts/header.php';
        require_once $this->viewPath . $view . '.php';
        require_once $this->viewPath . 'layouts/footer.php';
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        header('Location: ' . APP_URL . '/' . $url);
        exit;
    }

    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    protected function requireAuth() {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth/login');
        }
    }

    protected function logout() {
        session_unset();
        session_destroy();
        $this->redirect('auth/login');
    }

    protected function generateCSRFToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    protected function validateCSRFToken($token) {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validatePassword($password) {
        return strlen($password) >= PASSWORD_MIN_LENGTH;
    }
}

