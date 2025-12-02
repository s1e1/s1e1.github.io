// Gestion du drag and drop pour les cartes et listes

document.addEventListener('DOMContentLoaded', function() {
    initDragAndDrop();
    initSearch();
});

// ========== DRAG AND DROP ==========

function initDragAndDrop() {
    // Drag and drop des cartes
    const cards = document.querySelectorAll('.card-item');
    cards.forEach(card => {
        card.addEventListener('dragstart', handleCardDragStart);
        card.addEventListener('dragend', handleCardDragEnd);
    });

    // Drag and drop des listes
    const lists = document.querySelectorAll('.list-card');
    lists.forEach(list => {
        list.addEventListener('dragstart', handleListDragStart);
        list.addEventListener('dragend', handleListDragEnd);
    });

    // Zones de dépôt pour les cartes
    const cardContainers = document.querySelectorAll('.cards-container');
    cardContainers.forEach(container => {
        container.addEventListener('dragover', handleCardDragOver);
        container.addEventListener('drop', handleCardDrop);
        container.addEventListener('dragenter', handleCardDragEnter);
        container.addEventListener('dragleave', handleCardDragLeave);
    });

    // Zone de dépôt pour les listes
    const listsContainer = document.getElementById('lists-container');
    if (listsContainer) {
        listsContainer.addEventListener('dragover', handleListDragOver);
        listsContainer.addEventListener('drop', handleListDrop);
    }
}

let draggedCard = null;
let draggedList = null;

// Gestion du drag des cartes
function handleCardDragStart(e) {
    draggedCard = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.outerHTML);
}

function handleCardDragEnd(e) {
    this.classList.remove('dragging');
    document.querySelectorAll('.drop-zone').forEach(zone => {
        zone.classList.remove('drag-over');
    });
}

function handleCardDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleCardDragEnter(e) {
    this.classList.add('drag-over');
}

function handleCardDragLeave(e) {
    this.classList.remove('drag-over');
}

function handleCardDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    this.classList.remove('drag-over');
    
    if (draggedCard) {
        const newListId = parseInt(this.dataset.listId);
        const oldListId = parseInt(draggedCard.closest('.cards-container').dataset.listId);
        
        // Calculer la nouvelle position
        const cards = Array.from(this.querySelectorAll('.card-item'));
        const afterElement = getDragAfterElement(this, e.clientY);
        let newPosition = cards.length;
        
        if (afterElement == null) {
            this.appendChild(draggedCard);
        } else {
            this.insertBefore(draggedCard, afterElement);
            newPosition = cards.indexOf(afterElement);
        }
        
        // Envoyer la requête au serveur
        moveCard(draggedCard.dataset.cardId, oldListId, newListId, newPosition);
    }
    
    return false;
}

// Gestion du drag des listes
function handleListDragStart(e) {
    draggedList = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
}

function handleListDragEnd(e) {
    this.classList.remove('dragging');
}

function handleListDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    
    const afterElement = getDragAfterElement(this, e.clientX);
    if (afterElement == null) {
        this.appendChild(draggedList);
    } else {
        this.insertBefore(draggedList, afterElement);
    }
    
    return false;
}

function handleListDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    if (draggedList) {
        const listIds = Array.from(this.querySelectorAll('.list-card')).map(list => list.dataset.listId);
        reorderLists(listIds);
    }
    
    return false;
}

// Fonction utilitaire pour trouver l'élément après lequel insérer
function getDragAfterElement(container, x) {
    const draggableElements = [...container.querySelectorAll('.card-item:not(.dragging), .list-card:not(.dragging)')];
    
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = x - box.left - box.width / 2;
        
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// Fonctions API
async function moveCard(cardId, oldListId, newListId, newPosition) {
    try {
        const response = await fetch(APP_URL + '/card/move', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                card_id: cardId,
                old_list_id: oldListId,
                new_list_id: newListId,
                new_position: newPosition
            })
        });
        
        const data = await response.json();
        if (!data.success) {
            console.error('Erreur lors du déplacement:', data.error);
            location.reload(); // Recharger en cas d'erreur
        }
    } catch (error) {
        console.error('Erreur:', error);
        location.reload();
    }
}

async function reorderLists(listIds) {
    const boardId = document.querySelector('.board-container').dataset.boardId;
    
    try {
        const response = await fetch(APP_URL + '/list/reorder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                board_id: boardId,
                list_ids: listIds
            })
        });
        
        const data = await response.json();
        if (!data.success) {
            console.error('Erreur lors de la réorganisation:', data.error);
            location.reload();
        }
    } catch (error) {
        console.error('Erreur:', error);
        location.reload();
    }
}

// ========== GESTION DES LISTES ==========

function showAddListForm() {
    const modal = new bootstrap.Modal(document.getElementById('addListModal'));
    modal.show();
}

document.getElementById('addListForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(APP_URL + '/list/create', {
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

function editList(listId, currentTitle) {
    const newTitle = prompt('Nouveau titre:', currentTitle);
    if (newTitle && newTitle.trim() !== '') {
        const formData = new FormData();
        formData.append('title', newTitle.trim());
        
        fetch(APP_URL + '/list/update/' + listId, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Erreur lors de la mise à jour');
            }
        });
    }
}

function deleteList(listId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette liste ? Toutes les cartes seront également supprimées.')) {
        fetch(APP_URL + '/list/delete/' + listId, {
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

// ========== GESTION DES CARTES ==========

function showAddCardForm(listId) {
    const title = prompt('Titre de la carte:');
    if (title && title.trim() !== '') {
        const formData = new FormData();
        formData.append('list_id', listId);
        formData.append('title', title.trim());
        
        fetch(APP_URL + '/card/create', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Erreur lors de la création');
            }
        });
    }
}

function openCardModal(cardId) {
    // TODO: Implémenter la modal de détail de carte
    console.log('Ouvrir carte:', cardId);
}

// ========== RECHERCHE ==========

function initSearch() {
    const searchInput = document.getElementById('search-input');
    const filterSelect = document.getElementById('filter-due-date');
    
    if (searchInput) {
        searchInput.addEventListener('input', performSearch);
    }
    
    if (filterSelect) {
        filterSelect.addEventListener('change', performSearch);
    }
}

function performSearch() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const filter = document.getElementById('filter-due-date').value;
    const resultsContainer = document.getElementById('search-results');
    
    const cards = document.querySelectorAll('.card-item');
    const now = new Date();
    const tomorrow = new Date(now);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    let results = [];
    
    cards.forEach(card => {
        const title = card.querySelector('.card-title').textContent.toLowerCase();
        const dueDateElement = card.querySelector('.due-date-badge');
        let matches = true;
        
        // Filtre par titre
        if (searchTerm && !title.includes(searchTerm)) {
            matches = false;
        }
        
        // Filtre par date
        if (matches && filter && dueDateElement) {
            const dueDateText = dueDateElement.textContent.trim();
            const dueDate = parseDate(dueDateText);
            
            if (filter === 'overdue' && (!dueDate || dueDate >= now)) {
                matches = false;
            } else if (filter === 'due-soon' && (!dueDate || dueDate < now || dueDate > tomorrow)) {
                matches = false;
            } else if (filter === 'upcoming' && (!dueDate || dueDate <= tomorrow)) {
                matches = false;
            }
        }
        
        if (matches) {
            results.push({
                id: card.dataset.cardId,
                title: card.querySelector('.card-title').textContent,
                element: card
            });
        }
    });
    
    // Afficher les résultats
    if (results.length === 0) {
        resultsContainer.innerHTML = '<p class="text-muted">Aucun résultat trouvé.</p>';
    } else {
        let html = '<ul class="list-group">';
        results.forEach(result => {
            html += `<li class="list-group-item">
                <a href="#" onclick="scrollToCard(${result.id}); return false;">
                    ${escapeHtml(result.title)}
                </a>
            </li>`;
        });
        html += '</ul>';
        resultsContainer.innerHTML = html;
    }
}

function scrollToCard(cardId) {
    const card = document.querySelector(`[data-card-id="${cardId}"]`);
    if (card) {
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        card.style.backgroundColor = 'rgba(13, 110, 253, 0.2)';
        setTimeout(() => {
            card.style.backgroundColor = '';
        }, 2000);
    }
}

function parseDate(dateString) {
    const parts = dateString.split('/');
    if (parts.length === 3) {
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }
    return null;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Export
function exportBoard(boardId) {
    window.location.href = APP_URL + '/board/export/' + boardId;
}

// Import
document.getElementById('importBoardForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch(APP_URL + '/board/import', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = APP_URL + '/board/show/' + data.id;
        } else {
            alert(data.error || 'Erreur lors de l\'import');
        }
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
});

