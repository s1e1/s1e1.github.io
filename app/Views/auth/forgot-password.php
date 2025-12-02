<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow">
            <div class="card-body p-5">
                <h2 class="card-title text-center mb-4">Mot de passe oublié</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= url('auth/forgot-password') ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                        <small class="form-text text-muted">
                            Un lien de réinitialisation sera envoyé à cette adresse.
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">Envoyer le lien</button>
                </form>
                
                <div class="text-center">
                    <a href="<?= url('auth/login') ?>" class="text-decoration-none">Retour à la connexion</a>
                </div>
            </div>
        </div>
    </div>
</div>

