<?php
/**
 * Contrôleur des listes
 */

class ListController extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $boardId = (int)($_POST['board_id'] ?? 0);
            $title = $this->sanitize($_POST['title'] ?? '');

            if (empty($title)) {
                $this->json(['error' => 'Le titre est requis.'], 400);
                return;
            }

            // Vérifier l'accès au tableau
            if (!$this->hasBoardAccess($boardId)) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            // Récupérer la position maximale
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(position), 0) + 1 as next_position FROM lists WHERE board_id = ?");
            $stmt->execute([$boardId]);
            $result = $stmt->fetch();
            $position = $result['next_position'];

            $stmt = $this->db->prepare("INSERT INTO lists (board_id, title, position) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$boardId, $title, $position])) {
                $this->json(['success' => true, 'id' => $this->db->lastInsertId()]);
            } else {
                $this->json(['error' => 'Erreur lors de la création de la liste.'], 500);
            }
        }
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $list = $this->getList($id);
            
            if (!$list || !$this->hasBoardAccess($list['board_id'])) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            $title = $this->sanitize($_POST['title'] ?? '');

            if (empty($title)) {
                $this->json(['error' => 'Le titre est requis.'], 400);
                return;
            }

            $stmt = $this->db->prepare("UPDATE lists SET title = ? WHERE id = ?");
            
            if ($stmt->execute([$title, $id])) {
                $this->json(['success' => true]);
            } else {
                $this->json(['error' => 'Erreur lors de la mise à jour.'], 500);
            }
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $list = $this->getList($id);
            
            if (!$list || !$this->hasBoardAccess($list['board_id'])) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            $stmt = $this->db->prepare("DELETE FROM lists WHERE id = ?");
            
            if ($stmt->execute([$id])) {
                $this->json(['success' => true]);
            } else {
                $this->json(['error' => 'Erreur lors de la suppression.'], 500);
            }
        }
    }

    public function reorder() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $boardId = (int)($_POST['board_id'] ?? 0);
            $listIds = json_decode($_POST['list_ids'] ?? '[]', true);

            if (!$this->hasBoardAccess($boardId)) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            $this->db->beginTransaction();
            
            try {
                foreach ($listIds as $position => $listId) {
                    $stmt = $this->db->prepare("UPDATE lists SET position = ? WHERE id = ? AND board_id = ?");
                    $stmt->execute([$position, $listId, $boardId]);
                }

                $this->db->commit();
                $this->json(['success' => true]);
            } catch (Exception $e) {
                $this->db->rollBack();
                $this->json(['error' => 'Erreur lors de la réorganisation.'], 500);
            }
        }
    }

    private function getList($listId) {
        $stmt = $this->db->prepare("SELECT * FROM lists WHERE id = ?");
        $stmt->execute([$listId]);
        return $stmt->fetch();
    }

    private function hasBoardAccess($boardId) {
        $userId = $_SESSION['user_id'];
        $stmt = $this->db->prepare("
            SELECT b.id 
            FROM boards b
            LEFT JOIN collaborations c ON b.id = c.board_id
            WHERE b.id = ? AND (b.user_id = ? OR c.user_id = ?)
        ");
        $stmt->execute([$boardId, $userId, $userId]);
        return $stmt->fetch() !== false;
    }
}

