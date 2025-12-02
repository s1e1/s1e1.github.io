<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow">
            <div class="card-body p-5">
                <h2 class="card-title text-center mb-4">Réinitialiser le mot de passe</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                        <div class="mt-3">
                            <a href="<?= url('auth/login') ?>" class="btn btn-primary">Se connecter</a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?= url('auth/reset-password/' . ($token ?? '')) ?>" id="reset-form">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="<?= PASSWORD_MIN_LENGTH ?>">
                            <small class="form-text text-muted">Minimum <?= PASSWORD_MIN_LENGTH ?> caractères</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">Réinitialiser</button>
                    </form>
                <?php endif; ?>
                
                <div class="text-center">
                    <a href="<?= url('auth/login') ?>" class="text-decoration-none">Retour à la connexion</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('reset-form')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Les mots de passe ne correspondent pas.');
    }
});
</script>

