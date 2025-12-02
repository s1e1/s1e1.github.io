<div class="row justify-content-center" style="margin-top: 10%;">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-body p-5">
                <div class="row justify-content-center">
                    <img src="/assets/img/matrello.svg" alt="" style="width: 20rem;" class="mb-4">
                </div>
                <h2 class="card-title text-center mb-4">Créer un compte</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('auth/register') ?>" id="register-form">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="name" name="name" required minlength="2">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="<?= PASSWORD_MIN_LENGTH ?>">
                        <small class="form-text text-muted">Minimum <?= PASSWORD_MIN_LENGTH ?> caractères</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">S'inscrire</button>
                </form>

                <div class="text-center">
                    <p class="mb-0">Déjà un compte ?
                        <a href="<?= url('auth/login') ?>" class="text-decoration-none">Se connecter</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('register-form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Les mots de passe ne correspondent pas.');
        }
    });
</script>

<style>
    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        background-image: url("/assets/img/bg/<?= rand(1, 5) ?>.jpg");
        background-repeat: no-repeat;
        background-position: center center;
        background-attachment: fixed;
        background-size: cover;
        background-color: #222;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
    }

    /* Overlay pour améliorer le contraste du contenu */
    body::before {
        background: none;
    }

    /* Assurer que le contenu soit au-dessus de l'overlay */
    .row.justify-content-center {
        position: relative;
        z-index: 1;
    }

    /* Rendre la carte lisible sur le fond */
    .card.shadow {
        background-color: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    /* Petits écrans : éviter le background-attachment fixed qui peut poser problème */
    @media (max-width: 768px) {
        html {
            background-attachment: scroll;
        }
    }
</style>