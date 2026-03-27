<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Connexion — HeadCore CMS</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,300;0,400;0,500;1,400&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <style>
        .login-body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100dvh;
            background-color: var(--bg-canvas);
        }
        .login-card {
            width: 100%;
            max-width: 22rem;
            padding: var(--space-8);
        }
        .login-header {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            margin-bottom: var(--space-8);
        }
        .login-title {
            font-family: var(--font-display);
            font-style: italic;
            font-size: var(--text-2xl);
            font-weight: 400;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            margin-bottom: var(--space-1);
        }
        .login-subtitle {
            font-size: var(--text-sm);
            color: var(--text-muted);
        }
        .login-form .btn--primary {
            width: 100%;
            justify-content: center;
            padding: var(--space-3) var(--space-4);
            margin-top: var(--space-2);
        }
    </style>
</head>
<body class="admin-body login-body">

    <div class="card login-card">

        <div class="login-header">
            <span class="logo__mark" aria-hidden="true">HC</span>
            <div>
                <p class="login-title">Connexion</p>
                <p class="login-subtitle">HeadCore CMS</p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
        <div class="alert alert--error" role="alert" style="margin-bottom: var(--space-5);">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/login" class="login-form" novalidate>
            <div class="form-group">
                <label class="form-label" for="email">Adresse e-mail</label>
                <input
                    class="form-input"
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($prefillEmail ?? '') ?>"
                    autocomplete="email"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mot de passe</label>
                <input
                    class="form-input"
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="btn btn--primary">Se connecter</button>
        </form>

    </div>

</body>
</html>