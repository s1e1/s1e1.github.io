<?php
/**
 * Contrôleur des tableaux
 */

class BoardController extends Controller {
    
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }

    public function index() {
        // Récupérer tous les tableaux de l'utilisateur
        $userId = $_SESSION['user_id'];
        
        $stmt = $this->db->prepare("
            SELECT DISTINCT b.* 
            FROM boards b
            LEFT JOIN collaborations c ON b.id = c.board_id
            WHERE b.user_id = ? OR c.user_id = ?
            ORDER BY b.updated_at DESC
        ");
        $stmt->execute([$userId, $userId]);
        $boards = $stmt->fetchAll();

        $this->view('board/index', ['boards' => $boards]);
    }

    public function show($id) {
        $userId = $_SESSION['user_id'];
        
        // Vérifier l'accès au tableau
        $board = $this->getBoardWithAccess($id, $userId);
        if (!$board) {
            $this->redirect('board/index');
        }

        // Récupérer les listes avec leurs cartes
        $lists = $this->getListsWithCards($id);

        $this->view('board/show', [
            'board' => $board,
            'lists' => $lists
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $this->sanitize($_POST['title'] ?? '');
            $description = $this->sanitize($_POST['description'] ?? '');
            $isPrivate = isset($_POST['is_private']) ? 1 : 0;

            if (empty($title)) {
                $this->json(['error' => 'Le titre est requis.'], 400);
                return;
            }

            $userId = $_SESSION['user_id'];
            $stmt = $this->db->prepare("INSERT INTO boards (user_id, title, description, is_private) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$userId, $title, $description, $isPrivate])) {
                $this->json(['success' => true, 'id' => $this->db->lastInsertId()]);
            } else {
                $this->json(['error' => 'Erreur lors de la création du tableau.'], 500);
            }
        }
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $board = $this->getBoardWithAccess($id, $userId);
            
            if (!$board || $board['user_id'] != $userId) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            $title = $this->sanitize($_POST['title'] ?? '');
            $description = $this->sanitize($_POST['description'] ?? '');
            $isPrivate = isset($_POST['is_private']) ? 1 : 0;

            if (empty($title)) {
                $this->json(['error' => 'Le titre est requis.'], 400);
                return;
            }

            $stmt = $this->db->prepare("UPDATE boards SET title = ?, description = ?, is_private = ? WHERE id = ?");
            
            if ($stmt->execute([$title, $description, $isPrivate, $id])) {
                $this->json(['success' => true]);
            } else {
                $this->json(['error' => 'Erreur lors de la mise à jour.'], 500);
            }
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $board = $this->getBoardWithAccess($id, $userId);
            
            if (!$board || $board['user_id'] != $userId) {
                $this->json(['error' => 'Accès non autorisé.'], 403);
                return;
            }

            $stmt = $this->db->prepare("DELETE FROM boards WHERE id = ?");
            
            if ($stmt->execute([$id])) {
                $this->json(['success' => true]);
            } else {
                $this->json(['error' => 'Erreur lors de la suppression.'], 500);
            }
        }
    }

    public function export($id) {
        $userId = $_SESSION['user_id'];
        $board = $this->getBoardWithAccess($id, $userId);
        
        if (!$board) {
            $this->json(['error' => 'Tableau non trouvé.'], 404);
            return;
        }

        // Récupérer toutes les données du tableau
        $lists = $this->getListsWithCards($id);
        
        $exportData = [
            'board' => [
                'title' => $board['title'],
                'description' => $board['description'],
                'is_private' => (bool)$board['is_private'],
                'exported_at' => date('Y-m-d H:i:s')
            ],
            'lists' => []
        ];

        foreach ($lists as $list) {
            $listData = [
                'title' => $list['title'],
                'position' => (int)$list['position'],
                'cards' => []
            ];

            foreach ($list['cards'] as $card) {
                $cardData = [
                    'title' => $card['title'],
                    'description' => $card['description'],
                    'due_date' => $card['due_date'],
                    'is_completed' => (bool)$card['is_completed'],
                    'position' => (int)$card['position'],
                    'labels' => $card['labels'] ?? [],
                    'comments' => $card['comments'] ?? []
                ];
                $listData['cards'][] = $cardData;
            }

            $exportData['lists'][] = $listData;
        }

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="board_' . $id . '_' . date('Y-m-d') . '.json"');
        echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function import() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                $this->json(['error' => 'Erreur lors de l\'upload du fichier.'], 400);
                return;
            }

            $fileContent = file_get_contents($_FILES['file']['tmp_name']);
            $data = json_decode($fileContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->json(['error' => 'Fichier JSON invalide.'], 400);
                return;
            }

            // Valider la structure
            if (!isset($data['board']) || !isset($data['lists'])) {
                $this->json(['error' => 'Format de fichier invalide.'], 400);
                return;
            }

            // Créer le tableau
            $stmt = $this->db->prepare("INSERT INTO boards (user_id, title, description, is_private) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                $this->sanitize($data['board']['title']),
                $this->sanitize($data['board']['description'] ?? ''),
                isset($data['board']['is_private']) ? (int)$data['board']['is_private'] : 1
            ]);
            $boardId = $this->db->lastInsertId();

            // Créer les listes et cartes
            foreach ($data['lists'] as $listData) {
                $stmt = $this->db->prepare("INSERT INTO lists (board_id, title, position) VALUES (?, ?, ?)");
                $stmt->execute([
                    $boardId,
                    $this->sanitize($listData['title']),
                    $listData['position'] ?? 0
                ]);
                $listId = $this->db->lastInsertId();

                if (isset($listData['cards'])) {
                    foreach ($listData['cards'] as $cardData) {
                        $stmt = $this->db->prepare("
                            INSERT INTO cards (list_id, title, description, due_date, is_completed, position)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $listId,
                            $this->sanitize($cardData['title']),
                            $this->sanitize($cardData['description'] ?? ''),
                            !empty($cardData['due_date']) ? $cardData['due_date'] : null,
                            isset($cardData['is_completed']) ? (int)$cardData['is_completed'] : 0,
                            $cardData['position'] ?? 0
                        ]);
                        $cardId = $this->db->lastInsertId();

                        // Ajouter les étiquettes
                        if (isset($cardData['labels'])) {
                            foreach ($cardData['labels'] as $label) {
                                $stmt = $this->db->prepare("INSERT INTO card_labels (card_id, color, label) VALUES (?, ?, ?)");
                                $stmt->execute([$cardId, $label['color'], $this->sanitize($label['label'] ?? '')]);
                            }
                        }
                    }
                }
            }

            $this->json(['success' => true, 'id' => $boardId]);
        }
    }

    private function getBoardWithAccess($boardId, $userId) {
        $stmt = $this->db->prepare("
            SELECT b.* 
            FROM boards b
            LEFT JOIN collaborations c ON b.id = c.board_id
            WHERE b.id = ? AND (b.user_id = ? OR c.user_id = ?)
            LIMIT 1
        ");
        $stmt->execute([$boardId, $userId, $userId]);
        return $stmt->fetch();
    }

    private function getListsWithCards($boardId) {
        // Récupérer les listes
        $stmt = $this->db->prepare("SELECT * FROM lists WHERE board_id = ? ORDER BY position ASC");
        $stmt->execute([$boardId]);
        $lists = $stmt->fetchAll();

        // Pour chaque liste, récupérer les cartes
        foreach ($lists as &$list) {
            $stmt = $this->db->prepare("SELECT * FROM cards WHERE list_id = ? ORDER BY position ASC");
            $stmt->execute([$list['id']]);
            $cards = $stmt->fetchAll();

            // Pour chaque carte, récupérer les étiquettes et commentaires
            foreach ($cards as &$card) {
                // Étiquettes
                $stmt = $this->db->prepare("SELECT * FROM card_labels WHERE card_id = ?");
                $stmt->execute([$card['id']]);
                $card['labels'] = $stmt->fetchAll();

                // Commentaires
                $stmt = $this->db->prepare("
                    SELECT c.*, u.name as user_name 
                    FROM comments c
                    JOIN users u ON c.user_id = u.id
                    WHERE c.card_id = ?
                    ORDER BY c.created_at ASC
                ");
                $stmt->execute([$card['id']]);
                $card['comments'] = $stmt->fetchAll();
            }

            $list['cards'] = $cards;
        }

        return $lists;
    }
}

