<div class="row justify-content-center" style="margin-top: 14%;">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow">
            <div class="card-body p-5">
                <div class="row justify-content-center">
                    <img src="/assets/img/matrello.svg" alt="" style="width: 20rem;" class="mb-4">
                </div>
                <h2 class="card-title text-center mb-4">Connexion</h2>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= url('auth/login') ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3">
                        <a href="<?= url('auth/forgot-password') ?>" class="text-decoration-none">
                            Mot de passe oublié ?
                        </a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">Se connecter</button>
                </form>

                <div class="text-center">
                    <p class="mb-0">Pas encore de compte ?
                        <a href="<?= url('auth/register') ?>" class="text-decoration-none">S'inscrire</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
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