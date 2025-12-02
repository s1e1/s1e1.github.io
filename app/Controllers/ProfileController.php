<?php
/**
 * Contrôleur du profil utilisateur
 */

class ProfileController extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }

    public function index() {
        $userId = $_SESSION['user_id'];
        $stmt = $this->db->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $this->view('profile/index', ['user' => $user]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $name = $this->sanitize($_POST['name'] ?? '');
            $email = $this->sanitize($_POST['email'] ?? '');

            $errors = [];

            if (empty($name) || strlen($name) < 2) {
                $errors[] = "Le nom doit contenir au moins 2 caractères.";
            }

            if (empty($email) || !$this->validateEmail($email)) {
                $errors[] = "Format d'email invalide.";
            } else {
                // Vérifier si l'email est déjà utilisé par un autre utilisateur
                $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $userId]);
                if ($stmt->fetch()) {
                    $errors[] = "Cet email est déjà utilisé.";
                }
            }

            if (empty($errors)) {
                $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$name, $email, $userId])) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $this->json(['success' => true]);
                } else {
                    $this->json(['error' => 'Erreur lors de la mise à jour.'], 500);
                }
            } else {
                $this->json(['errors' => $errors], 400);
            }
        }
    }

    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $errors = [];

            // Vérifier le mot de passe actuel
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!password_verify($currentPassword, $user['password'])) {
                $errors[] = "Mot de passe actuel incorrect.";
            }

            if (!$this->validatePassword($newPassword)) {
                $errors[] = "Le nouveau mot de passe doit contenir au moins " . PASSWORD_MIN_LENGTH . " caractères.";
            }

            if ($newPassword !== $confirmPassword) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }

            if (empty($errors)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
                
                if ($stmt->execute([$hashedPassword, $userId])) {
                    $this->json(['success' => true]);
                } else {
                    $this->json(['error' => 'Erreur lors de la mise à jour.'], 500);
                }
            } else {
                $this->json(['errors' => $errors], 400);
            }
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $password = $_POST['password'] ?? '';

            // Vérifier le mot de passe
            $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (!password_verify($password, $user['password'])) {
                $this->json(['error' => 'Mot de passe incorrect.'], 400);
                return;
            }

            // Supprimer l'utilisateur (cascade supprimera toutes les données associées)
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            
            if ($stmt->execute([$userId])) {
                parent::logout();
            } else {
                $this->json(['error' => 'Erreur lors de la suppression.'], 500);
            }
        }
    }
}

