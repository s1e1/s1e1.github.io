<?php
/**
 * Contrôleur d'authentification
 */

class AuthController extends Controller {
    
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        if ($this->isLoggedIn()) {
            $this->redirect('board/index');
        }
        $this->redirect('auth/login');
    }

    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('board/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validation
            if (empty($email) || empty($password)) {
                $error = "Veuillez remplir tous les champs.";
            } elseif (!$this->validateEmail($email)) {
                $error = "Format d'email invalide.";
            } else {
                // Vérifier les identifiants
                $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Connexion réussie
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['last_activity'] = time();

                    // Enregistrer la session en base
                    $this->saveSession($user['id']);

                    $this->redirect('board/index');
                } else {
                    $error = "Email ou mot de passe incorrect.";
                }
            }
        }

        $this->view('auth/login', ['error' => $error ?? null]);
    }

    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect('board/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $this->sanitize($_POST['name'] ?? '');
            $email = $this->sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Validation
            $errors = [];
            
            if (empty($name) || strlen($name) < 2) {
                $errors[] = "Le nom doit contenir au moins 2 caractères.";
            }
            
            if (empty($email) || !$this->validateEmail($email)) {
                $errors[] = "Format d'email invalide.";
            } else {
                // Vérifier si l'email existe déjà
                $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $errors[] = "Cet email est déjà utilisé.";
                }
            }
            
            if (!$this->validatePassword($password)) {
                $errors[] = "Le mot de passe doit contenir au moins " . PASSWORD_MIN_LENGTH . " caractères.";
            }
            
            if ($password !== $confirm_password) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }

            if (empty($errors)) {
                // Créer l'utilisateur
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                
                if ($stmt->execute([$name, $email, $hashedPassword])) {
                    // Connexion automatique
                    $userId = $this->db->lastInsertId();
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['last_activity'] = time();
                    
                    $this->saveSession($userId);
                    $this->redirect('board/index');
                } else {
                    $errors[] = "Erreur lors de la création du compte.";
                }
            }
        }

        $this->view('auth/register', ['errors' => $errors ?? []]);
    }

    public function logout() {
        if ($this->isLoggedIn()) {
            // Supprimer la session de la base
            $this->deleteSession();
        }
        parent::logout();
    }

    public function forgotPassword() {
        if ($this->isLoggedIn()) {
            $this->redirect('board/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->sanitize($_POST['email'] ?? '');
            
            if (empty($email) || !$this->validateEmail($email)) {
                $error = "Format d'email invalide.";
            } else {
                // Vérifier si l'utilisateur existe
                $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    // Générer un token
                    $token = bin2hex(random_bytes(32));
                    $expiresAt = date('Y-m-d H:i:s', time() + PASSWORD_RESET_EXPIRY);
                    
                    // Supprimer les anciens tokens
                    $stmt = $this->db->prepare("DELETE FROM password_resets WHERE user_id = ?");
                    $stmt->execute([$user['id']]);
                    
                    // Insérer le nouveau token
                    $stmt = $this->db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                    $stmt->execute([$user['id'], $token, $expiresAt]);
                    
                    // Envoyer l'email (à implémenter)
                    // $this->sendPasswordResetEmail($email, $token);
                    
                    $success = "Un email de réinitialisation a été envoyé.";
                } else {
                    // Ne pas révéler si l'email existe ou non (sécurité)
                    $success = "Si cet email existe, un lien de réinitialisation a été envoyé.";
                }
            }
        }

        $this->view('auth/forgot-password', [
            'error' => $error ?? null,
            'success' => $success ?? null
        ]);
    }

    public function resetPassword($token = null) {
        if ($this->isLoggedIn()) {
            $this->redirect('board/index');
        }

        if (empty($token)) {
            $this->redirect('auth/forgot-password');
        }

        // Vérifier le token
        $stmt = $this->db->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            $error = "Lien invalide ou expiré.";
            $this->view('auth/reset-password', ['error' => $error, 'token' => null]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (!$this->validatePassword($password)) {
                $error = "Le mot de passe doit contenir au moins " . PASSWORD_MIN_LENGTH . " caractères.";
            } elseif ($password !== $confirm_password) {
                $error = "Les mots de passe ne correspondent pas.";
            } else {
                // Mettre à jour le mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $reset['user_id']]);
                
                // Supprimer le token
                $stmt = $this->db->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->execute([$token]);
                
                $success = "Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
                $this->view('auth/reset-password', ['success' => $success, 'token' => null]);
                return;
            }
        }

        $this->view('auth/reset-password', [
            'error' => $error ?? null,
            'token' => $token
        ]);
    }

    private function saveSession($userId) {
        $sessionId = session_id();
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt = $this->db->prepare("
            INSERT INTO sessions (id, user_id, ip_address, user_agent, last_activity)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE last_activity = NOW()
        ");
        $stmt->execute([$sessionId, $userId, $ipAddress, $userAgent]);
    }

    private function deleteSession() {
        $sessionId = session_id();
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->execute([$sessionId]);
    }
}

