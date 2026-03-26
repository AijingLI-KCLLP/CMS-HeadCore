<?php
use Core\Auth\Acl;

$pageTitle      = $pageTitle      ?? 'Back-office';
$currentSection = $currentSection ?? '';
$userEmail      = $userEmail      ?? '';
$userRole       = $userRole       ?? '';
$content        = $content        ?? '';


$isActive = static fn(string $section): string =>
    $currentSection === $section ? 'nav__link--active' : '';


$roleLabel = match ($userRole) {
    'admin'  => 'Administrateur',
    'editor' => 'Éditeur',
    'author' => 'Auteur',
    'reader' => 'Lecteur',
    default  => ucfirst($userRole),
};

$roleBadgeClass = match ($userRole) {
    'admin'  => 'badge--admin',
    'editor' => 'badge--editor',
    'author' => 'badge--author',
    default  => 'badge--reader',
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($pageTitle) ?> — HeadCore CMS</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,300;0,400;0,500;1,400&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

     <!-- HEADER -->
    <header class="admin-header" role="banner">
        <div class="admin-header__inner">

            <a href="/admin" class="admin-header__logo" aria-label="HeadCore CMS — Accueil">
                <span class="logo__mark" aria-hidden="true">HC</span>
                <span class="logo__name">HeadCore</span>
                <span class="logo__env">CMS</span>
            </a>

            <!-- Navigation principale  -->
            <nav class="admin-nav" aria-label="Navigation principale">
                <ul class="nav__list" role="list">

                    <?php if (Acl::can($userRole, 'content.read')): ?>
                    <li class="nav__item">
                        <a href="/admin/contents"
                           class="nav__link <?= $isActive('contents') ?>"
                           aria-current="<?= $currentSection === 'contents' ? 'page' : 'false' ?>">
                            <span class="nav__icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="1" y="1" width="6" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
                                    <rect x="9" y="1" width="6" height="4" rx="1" stroke="currentColor" stroke-width="1.5"/>
                                    <rect x="9" y="7" width="6" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
                                    <rect x="1" y="11" width="6" height="4" rx="1" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </span>
                            Contenus
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (Acl::can($userRole, 'media.upload')): ?>
                    <li class="nav__item">
                        <a href="/admin/media"
                           class="nav__link <?= $isActive('media') ?>"
                           aria-current="<?= $currentSection === 'media' ? 'page' : 'false' ?>">
                            <span class="nav__icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="1" y="3" width="14" height="10" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                                    <circle cx="5.5" cy="6.5" r="1.5" stroke="currentColor" stroke-width="1.25"/>
                                    <path d="M1 10.5L4.5 7.5L7 10L10 7L15 11" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            Médias
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (Acl::can($userRole, 'user.manage')): ?>
                    <li class="nav__item">
                        <a href="/admin/users"
                           class="nav__link <?= $isActive('users') ?>"
                           aria-current="<?= $currentSection === 'users' ? 'page' : 'false' ?>">
                            <span class="nav__icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="6" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M1 13.5C1 11.015 3.239 9 6 9s5 2.015 5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <path d="M11.5 7.5L13 9L15.5 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            Utilisateurs
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (Acl::can($userRole, 'settings.manage')): ?>
                    <li class="nav__item">
                        <a href="/admin/settings"
                           class="nav__link <?= $isActive('settings') ?>"
                           aria-current="<?= $currentSection === 'settings' ? 'page' : 'false' ?>">
                            <span class="nav__icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="8" cy="8" r="2.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 1v2M8 13v2M1 8h2M13 8h2M3.05 3.05l1.414 1.414M11.536 11.536l1.414 1.414M3.05 12.95l1.414-1.414M11.536 4.464l1.414-1.414" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"/>
                                </svg>
                            </span>
                            Paramètres
                        </a>
                    </li>
                    <?php endif; ?>

                </ul>
            </nav>

            <!-- Profil utilisateur et déconnexion -->
            <div class="admin-header__user">
                <div class="user-profile">
                    <span class="user-profile__avatar" aria-hidden="true">
                        <?= htmlspecialchars(mb_strtoupper(mb_substr($userEmail, 0, 1))) ?>
                    </span>
                    <div class="user-profile__info">
                        <span class="user-profile__email"
                              title="<?= htmlspecialchars($userEmail) ?>">
                            <?= htmlspecialchars($userEmail) ?>
                        </span>
                        <span class="badge <?= $roleBadgeClass ?>"><?= htmlspecialchars($roleLabel) ?></span>
                    </div>
                </div>

                <form method="POST" action="/logout" class="logout-form">
                    <!-- Token CSRF à implémenter -->
                    <button type="submit" class="btn-logout" aria-label="Se déconnecter">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M6 2H3a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M10.5 5L14 8l-3.5 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 8H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                        <span>Déconnexion</span>
                    </button>
                </form>
            </div>

        </div>
    </header>

    <!--  CORPS DE PAGE — sidebar contextuelle + contenu -->
    <div class="admin-shell">

        <!-- Sidebar de navigation secondaire -->
        <?php if ($currentSection !== ''): ?>
        <aside class="admin-sidebar" aria-label="Navigation secondaire">
            <nav>

                <?php if ($currentSection === 'contents'): ?>
                <ul class="sidebar__list" role="list">
                    <li class="sidebar__group-label">Contenus</li>
                    <?php if (Acl::can($userRole, 'content.read')): ?>
                    <li><a href="/admin/contents" class="sidebar__link">Tous les contenus</a></li>
                    <?php endif; ?>
                    <?php if (Acl::can($userRole, 'content.create')): ?>
                    <li><a href="/admin/contents/new" class="sidebar__link">Nouveau contenu</a></li>
                    <?php endif; ?>
                    <?php if (Acl::can($userRole, 'content.publish')): ?>
                    <li><a href="/admin/contents?status=pending" class="sidebar__link">En attente de relecture</a></li>
                    <li><a href="/admin/contents?status=published" class="sidebar__link">Publiés</a></li>
                    <?php endif; ?>
                    <?php if (Acl::can($userRole, 'content.archive')): ?>
                    <li><a href="/admin/contents?status=archived" class="sidebar__link">Archivés</a></li>
                    <?php endif; ?>
                </ul>

                <?php elseif ($currentSection === 'media'): ?>
                <ul class="sidebar__list" role="list">
                    <li class="sidebar__group-label">Médiathèque</li>
                    <li><a href="/admin/media" class="sidebar__link">Tous les médias</a></li>
                    <?php if (Acl::can($userRole, 'media.upload')): ?>
                    <li><a href="/admin/media/upload" class="sidebar__link">Importer un fichier</a></li>
                    <?php endif; ?>
                </ul>

                <?php elseif ($currentSection === 'users'): ?>
                <ul class="sidebar__list" role="list">
                    <li class="sidebar__group-label">Utilisateurs</li>
                    <li><a href="/admin/users" class="sidebar__link">Tous les utilisateurs</a></li>
                    <li class="sidebar__group-label" style="margin-top: 1.5rem;">Rôles</li>
                    <li><a href="/admin/users?role=admin" class="sidebar__link">Administrateurs</a></li>
                    <li><a href="/admin/users?role=editor" class="sidebar__link">Éditeurs</a></li>
                    <li><a href="/admin/users?role=author" class="sidebar__link">Auteurs</a></li>
                    <li><a href="/admin/users?role=reader" class="sidebar__link">Lecteurs</a></li>
                </ul>

                <?php elseif ($currentSection === 'settings'): ?>
                <ul class="sidebar__list" role="list">
                    <li class="sidebar__group-label">Paramètres</li>
                    <li><a href="/admin/settings" class="sidebar__link">Général</a></li>
                    <li><a href="/admin/settings/security" class="sidebar__link">Sécurité</a></li>
                </ul>
                <?php endif; ?>

            </nav>
        </aside>
        <?php endif; ?>

        <!-- Zone de contenu principal de la page -->
        <main class="admin-main"
              id="main-content"
              role="main"
              aria-label="Contenu principal"
              tabindex="-1">

            <div class="admin-main__header">
                <h1 class="page-title"><?= htmlspecialchars($pageTitle) ?></h1>
            </div>

            <div class="admin-main__body">
                <?= $content ?>
            </div>

        </main>
    </div>

    <!-- FOOTER -->
    <footer class="admin-footer" role="contentinfo">
        <p class="admin-footer__text">
            HeadCore CMS &mdash; connecté en tant que
            <strong><?= htmlspecialchars($userEmail) ?></strong>
            <span class="badge <?= $roleBadgeClass ?>"><?= htmlspecialchars($roleLabel) ?></span>
        </p>
    </footer>

</body>
</html>
