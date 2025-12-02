<?php
$additional_js = ['board'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><?= htmlspecialchars($board['title']) ?></h1>
        <?php if ($board['description']): ?>
            <p class="text-muted"><?= htmlspecialchars($board['description']) ?></p>
        <?php endif; ?>
    </div>
    <div class="btn-group">
        <button class="btn btn-outline-primary" onclick="exportBoard(<?= $board['id'] ?>)">
            <i class="bi bi-download"></i> Exporter
        </button>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#importBoardModal">
            <i class="bi bi-upload"></i> Importer
        </button>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#searchModal">
            <i class="bi bi-search"></i> Rechercher
        </button>
    </div>
</div>

<div class="board-container" data-board-id="<?= $board['id'] ?>">
    <div class="lists-container d-flex gap-3 overflow-auto pb-3" id="lists-container">
        <?php foreach ($lists as $list): ?>
            <div class="list-card" data-list-id="<?= $list['id'] ?>" draggable="true">
                <div class="list-header d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0"><?= htmlspecialchars($list['title']) ?></h6>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-sm btn-outline-secondary" onclick="editList(<?= $list['id'] ?>, '<?= htmlspecialchars($list['title'], ENT_QUOTES) ?>')">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteList(<?= $list['id'] ?>)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                
                <div class="cards-container" data-list-id="<?= $list['id'] ?>">
                    <?php foreach ($list['cards'] as $card): ?>
                        <?php include '../app/Views/board/card.php'; ?>
                    <?php endforeach; ?>
                </div>
                
                <button class="btn btn-sm btn-outline-primary w-100 mt-2" onclick="showAddCardForm(<?= $list['id'] ?>)">
                    <i class="bi bi-plus"></i> Ajouter une carte
                </button>
            </div>
        <?php endforeach; ?>
    </div>
    
    <button class="btn btn-primary mt-3" onclick="showAddListForm()">
        <i class="bi bi-plus-circle"></i> Ajouter une liste
    </button>
</div>

<!-- Modal d'ajout de liste -->
<div class="modal fade" id="addListModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle liste</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addListForm">
                <input type="hidden" name="board_id" value="<?= $board['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="list_title" class="form-label">Titre *</label>
                        <input type="text" class="form-control" id="list_title" name="title" required>
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

<!-- Modal d'import -->
<div class="modal fade" id="importBoardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importer un tableau</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importBoardForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="import_file" class="form-label">Fichier JSON *</label>
                        <input type="file" class="form-control" id="import_file" name="file" accept=".json" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Importer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de recherche -->
<div class="modal fade" id="searchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rechercher des cartes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="search-input" placeholder="Rechercher par titre...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Filtrer par date d'échéance</label>
                    <select class="form-select" id="filter-due-date">
                        <option value="">Toutes</option>
                        <option value="overdue">En retard</option>
                        <option value="due-soon">Imminentes (≤ 24h)</option>
                        <option value="upcoming">À venir</option>
                    </select>
                </div>
                <div id="search-results"></div>
            </div>
        </div>
    </div>
</div>

