<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow">
            <div class="card-header">
                <h4 class="mb-0">Mon profil</h4>
            </div>
            <div class="card-body">
                <form id="profileForm">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($user['name']) ?>" required minlength="2">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted">
                            Membre depuis le <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </form>
                
                <hr>
                
                <h5>Changer le mot de passe</h5>
                <form id="passwordForm">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mot de passe actuel</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               required minlength="<?= PASSWORD_MIN_LENGTH ?>">
                        <small class="form-text text-muted">Minimum <?= PASSWORD_MIN_LENGTH ?> caractères</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">Changer le mot de passe</button>
                </form>
                
                <hr>
                
                <h5 class="text-danger">Zone de danger</h5>
                <p class="text-muted">La suppression de votre compte est irréversible. Toutes vos données seront définitivement supprimées.</p>
                <form id="deleteAccountForm">
                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Confirmer avec votre mot de passe</label>
                        <input type="password" class="form-control" id="delete_password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous absolument sûr ? Cette action est irréversible !')">
                        Supprimer mon compte
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('profileForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?= url('profile/update') ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Profil mis à jour avec succès !');
            location.reload();
        } else {
            alert(data.errors ? data.errors.join('\n') : data.error || 'Erreur lors de la mise à jour');
        }
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
});

document.getElementById('passwordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?= url('profile/change-password') ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Mot de passe changé avec succès !');
            this.reset();
        } else {
            alert(data.errors ? data.errors.join('\n') : data.error || 'Erreur lors du changement');
        }
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
});

document.getElementById('deleteAccountForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('<?= url('profile/delete') ?>', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success || response.ok) {
            window.location.href = '<?= url('auth/login') ?>';
        } else {
            alert(data.error || 'Erreur lors de la suppression');
        }
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
});
</script>

