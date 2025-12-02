<?php
/**
 * Contrôleur des cartes
 */

class CardController extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $listId = (int)($_POST['list_id'] ?? 0);
            $title = $this->sanitize($_POST['title'] ?? '');

            if (empty($title)) {
                $this->json(['error' => 'Le titre est requis.'], 400);
                return;
            }

            // Vérifier l'accès à la liste
            if (!$this->hasListAccess($listId)) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            // Récupérer la position maximale
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(position), 0) + 1 as next_position FROM cards WHERE list_id = ?");
            $stmt->execute([$listId]);
            $result = $stmt->fetch();
            $position = $result['next_position'];

            $stmt = $this->db->prepare("INSERT INTO cards (list_id, title, position) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$listId, $title, $position])) {
                $this->json(['success' => true, 'id' => $this->db->lastInsertId()]);
            } else {
                $this->json(['error' => 'Erreur lors de la création de la carte.'], 500);
            }
        }
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $card = $this->getCard($id);
            
            if (!$card || !$this->hasListAccess($card['list_id'])) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            $title = $this->sanitize($_POST['title'] ?? '');
            $description = $this->sanitize($_POST['description'] ?? '');
            $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            $isCompleted = isset($_POST['is_completed']) ? 1 : 0;

            if (empty($title)) {
                $this->json(['error' => 'Le titre est requis.'], 400);
                return;
            }

            $stmt = $this->db->prepare("
                UPDATE cards 
                SET title = ?, description = ?, due_date = ?, is_completed = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([$title, $description, $dueDate, $isCompleted, $id])) {
                $this->json(['success' => true]);
            } else {
                $this->json(['error' => 'Erreur lors de la mise à jour.'], 500);
            }
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $card = $this->getCard($id);
            
            if (!$card || !$this->hasListAccess($card['list_id'])) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            $stmt = $this->db->prepare("DELETE FROM cards WHERE id = ?");
            
            if ($stmt->execute([$id])) {
                $this->json(['success' => true]);
            } else {
                $this->json(['error' => 'Erreur lors de la suppression.'], 500);
            }
        }
    }

    public function move() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cardId = (int)($_POST['card_id'] ?? 0);
            $newListId = (int)($_POST['new_list_id'] ?? 0);
            $newPosition = (int)($_POST['new_position'] ?? 0);

            $card = $this->getCard($cardId);
            
            if (!$card || !$this->hasListAccess($card['list_id']) || !$this->hasListAccess($newListId)) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            // Déplacer la carte
            $this->db->beginTransaction();
            
            try {
                // Réorganiser les positions dans l'ancienne liste
                $stmt = $this->db->prepare("UPDATE cards SET position = position - 1 WHERE list_id = ? AND position > ?");
                $stmt->execute([$card['list_id'], $card['position']]);

                // Réorganiser les positions dans la nouvelle liste
                $stmt = $this->db->prepare("UPDATE cards SET position = position + 1 WHERE list_id = ? AND position >= ?");
                $stmt->execute([$newListId, $newPosition]);

                // Mettre à jour la carte
                $stmt = $this->db->prepare("UPDATE cards SET list_id = ?, position = ? WHERE id = ?");
                $stmt->execute([$newListId, $newPosition, $cardId]);

                $this->db->commit();
                $this->json(['success' => true]);
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->json(['error' => 'Erreur lors du déplacement.'], 500);
            }
        }
    }

    public function reorder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $listId = (int)($_POST['list_id'] ?? 0);
            $cardIds = json_decode($_POST['card_ids'] ?? '[]', true);

            if (!$this->hasListAccess($listId)) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            $this->db->beginTransaction();
            
            try {
                foreach ($cardIds as $position => $cardId) {
                    $stmt = $this->db->prepare("UPDATE cards SET position = ? WHERE id = ? AND list_id = ?");
                    $stmt->execute([$position, $cardId, $listId]);
                }

                $this->db->commit();
                $this->json(['success' => true]);
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->json(['error' => 'Erreur lors de la réorganisation.'], 500);
            }
        }
    }

    public function addLabel() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cardId = (int)($_POST['card_id'] ?? 0);
            $color = $this->sanitize($_POST['color'] ?? '');
            $label = $this->sanitize($_POST['label'] ?? '');

            $card = $this->getCard($cardId);
            
            if (!$card || !$this->hasListAccess($card['list_id'])) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            // Valider la couleur
            if (!array_key_exists($color, LABEL_COLORS)) {
                $this->json(['error' => 'Couleur invalide.'], 400);
                return;
            }

            $stmt = $this->db->prepare("INSERT INTO card_labels (card_id, color, label) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$cardId, $color, $label])) {
                $this->json(['success' => true, 'id' => $this->db->lastInsertId()]);
            } else {
                $this->json(['error' => 'Erreur lors de l\'ajout de l\'étiquette.'], 500);
            }
        }
    }

    public function removeLabel($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $this->db->prepare("
                SELECT cl.*, c.list_id 
                FROM card_labels cl
                JOIN cards c ON cl.card_id = c.id
                WHERE cl.id = ?
            ");
            $stmt->execute([$id]);
            $label = $stmt->fetch();

            if (!$label || !$this->hasListAccess($label['list_id'])) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            $stmt = $this->db->prepare("DELETE FROM card_labels WHERE id = ?");
            
            if ($stmt->execute([$id])) {
                $this->json(['success' => true]);
            } else {
                $this->json(['error' => 'Erreur lors de la suppression.'], 500);
            }
        }
    }

    public function addComment() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cardId = (int)($_POST['card_id'] ?? 0);
            $content = $this->sanitize($_POST['content'] ?? '');

            $card = $this->getCard($cardId);
            
            if (!$card || !$this->hasListAccess($card['list_id'])) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            if (empty($content)) {
                $this->json(['error' => 'Le commentaire ne peut pas être vide.'], 400);
                return;
            }

            $userId = $_SESSION['user_id'];
            $stmt = $this->db->prepare("INSERT INTO comments (card_id, user_id, content) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$cardId, $userId, $content])) {
                // Récupérer le commentaire avec le nom de l'utilisateur
                $commentId = $this->db->lastInsertId();
                $stmt = $this->db->prepare("
                    SELECT c.*, u.name as user_name 
                    FROM comments c
                    JOIN users u ON c.user_id = u.id
                    WHERE c.id = ?
                ");
                $stmt->execute([$commentId]);
                $comment = $stmt->fetch();
                
                $this->json(['success' => true, 'comment' => $comment]);
            } else {
                $this->json(['error' => 'Erreur lors de l\'ajout du commentaire.'], 500);
            }
        }
    }

    private function getCard($cardId) {
        $stmt = $this->db->prepare("SELECT * FROM cards WHERE id = ?");
        $stmt->execute([$cardId]);
        return $stmt->fetch();
    }

    private function hasListAccess($listId) {
        $userId = $_SESSION['user_id'];
        $stmt = $this->db->prepare("
            SELECT b.id 
            FROM lists l
            JOIN boards b ON l.board_id = b.id
            LEFT JOIN collaborations c ON b.id = c.board_id
            WHERE l.id = ? AND (b.user_id = ? OR c.user_id = ?)
        ");
        $stmt->execute([$listId, $userId, $userId]);
        return $stmt->fetch() !== false;
    }
}

