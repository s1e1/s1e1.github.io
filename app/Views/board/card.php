<?php
// Calculer le statut de la carte
$cardStatus = 'normal';
if ($card['due_date']) {
    $dueDate = new DateTime($card['due_date']);
    $now = new DateTime();
    $diff = $now->diff($dueDate);
    
    if ($card['is_completed']) {
        $cardStatus = 'completed';
    } elseif ($dueDate < $now) {
        $cardStatus = 'overdue';
    } elseif ($diff->days <= 1 && $diff->invert == 0) {
        $cardStatus = 'due-soon';
    }
}
?>

<div class="card mb-2 card-item <?= $cardStatus ?>" 
     data-card-id="<?= $card['id'] ?>" 
     draggable="true"
     onclick="openCardModal(<?= $card['id'] ?>)">
    <div class="card-body p-2">
        <?php if (!empty($card['labels'])): ?>
            <div class="mb-1">
                <?php foreach ($card['labels'] as $label): ?>
                    <span class="badge" style="background-color: <?= htmlspecialchars($label['color']) ?>">
                        <?= htmlspecialchars($label['label'] ?: '') ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <h6 class="card-title mb-1 <?= $card['is_completed'] ? 'text-decoration-line-through text-muted' : '' ?>">
            <?= htmlspecialchars($card['title']) ?>
        </h6>
        
        <?php if ($card['due_date']): ?>
            <small class="d-block mb-1">
                <i class="bi bi-calendar"></i> 
                <span class="due-date-badge <?= $cardStatus ?>">
                    <?= date('d/m/Y', strtotime($card['due_date'])) ?>
                </span>
            </small>
        <?php endif; ?>
        
        <?php if (!empty($card['comments'])): ?>
            <small class="text-muted">
                <i class="bi bi-chat"></i> <?= count($card['comments']) ?>
            </small>
        <?php endif; ?>
    </div>
</div>

