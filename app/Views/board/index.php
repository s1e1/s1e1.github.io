<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Mes tableaux</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBoardModal">
        <i class="bi bi-plus-circle"></i> Nouveau tableau
    </button>
</div>

<div class="row g-3">
    <?php if (empty($boards)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Vous n'avez pas encore de tableau. Créez-en un pour commencer !
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($boards as $board): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 board-card">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($board['title']) ?></h5>
                        <?php if ($board['description']): ?>
                            <p class="card-text text-muted small"><?= htmlspecialchars($board['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <a href="<?= url('board/show/' . $board['id']) ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ouvrir
                            </a>
                            <button class="btn btn-sm btn-outline-secondary" onclick="exportBoard(<?= $board['id'] ?>)">
                                <i class="bi bi-download"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteBoard(<?= $board['id'] ?>)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal de création de tableau -->
<div class="modal fade" id="createBoardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau tableau</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createBoardForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="board_title" class="form-label">Titre *</label>
                        <input type="text" class="form-control" id="board_title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="board_description" class="form-label">Description</label>
                        <textarea class="form-control" id="board_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="board_private" name="is_private" checked>
                        <label class="form-check-label" for="board_private">
                            Tableau privé
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('createBoardForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?= url('board/create') ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erreur lors de la création');
        }
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
});

function deleteBoard(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce tableau ?')) {
        fetch('<?= url('board/delete') ?>/' + id, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Erreur lors de la suppression');
            }
        });
    }
}

function exportBoard(id) {
    window.location.href = '<?= url('board/export') ?>/' + id;
}
</script>

