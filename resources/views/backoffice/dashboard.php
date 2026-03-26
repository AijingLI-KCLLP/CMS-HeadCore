<?php

use Core\Auth\Acl;

$contents = $stats['contents'] ?? ['draft' => 0, 'published' => 0, 'archived' => 0, 'total' => 0];
$users    = $stats['users']    ?? ['total' => 0];
$media    = $stats['media']    ?? 0;

// Salutation selon l'heure serveur
$hour = (int) date('H');
$greeting = match (true) {
    $hour >= 5  && $hour < 12 => 'Bonjour',
    $hour >= 12 && $hour < 18 => 'Bon après-midi',
    default                   => 'Bonsoir',
};

// Libellé du rôle
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

$displayName = htmlspecialchars(
    strstr($userEmail, '@', before_needle: true) ?: $userEmail
);
?>


<section class="dashboard-welcome" aria-label="Message de bienvenue">
    <div class="welcome__text">
        <p class="welcome__greeting"><?= $greeting ?>,</p>
        <h2 class="welcome__name"><?= $displayName ?></h2>
        <p class="welcome__role">
            Connecté en tant que
            <span class="badge <?= $roleBadgeClass ?>"><?= htmlspecialchars($roleLabel) ?></span>
        </p>
    </div>
    <p class="welcome__date">
        <?= ucfirst(strftime('%A %e %B %Y')) ?>
    </p>
</section>


<section class="dashboard-section" aria-labelledby="stats-heading">
    <h3 class="dashboard-section__title" id="stats-heading">Vue d'ensemble</h3>

    <div class="stats-grid">

        <!-- Contenus — brouillons -->
        <?php if (Acl::can($userRole, 'content.read')): ?>
        <a href="/admin/contents?status=draft" class="stat-card stat-card--link" aria-label="<?= $contents['draft'] ?> brouillons">
            <div class="stat-card__icon stat-card__icon--draft" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 4h12M4 8h8M4 12h10M4 16h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="stat-card__body">
                <span class="stat-card__value"><?= number_format($contents['draft']) ?></span>
                <span class="stat-card__label">Brouillons</span>
            </div>
            <span class="stat-card__arrow" aria-hidden="true">→</span>
        </a>

        <!-- Contenus — publiés -->
        <a href="/admin/contents?status=published" class="stat-card stat-card--link" aria-label="<?= $contents['published'] ?> publiés">
            <div class="stat-card__icon stat-card__icon--published" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M6.5 10.5L9 13l4.5-5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="stat-card__body">
                <span class="stat-card__value"><?= number_format($contents['published']) ?></span>
                <span class="stat-card__label">Publiés</span>
            </div>
            <span class="stat-card__arrow" aria-hidden="true">→</span>
        </a>

        <!-- Contenus — archivés -->
        <?php if (Acl::can($userRole, 'content.archive')): ?>
        <a href="/admin/contents?status=archived" class="stat-card stat-card--link" aria-label="<?= $contents['archived'] ?> archivés">
            <div class="stat-card__icon stat-card__icon--archived" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="5" width="16" height="3" rx="1" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M4 8v8a1 1 0 001 1h10a1 1 0 001-1V8" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M8 12h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="stat-card__body">
                <span class="stat-card__value"><?= number_format($contents['archived']) ?></span>
                <span class="stat-card__label">Archivés</span>
            </div>
            <span class="stat-card__arrow" aria-hidden="true">→</span>
        </a>
        <?php endif; ?>

        <!-- Total contenus -->
        <div class="stat-card stat-card--total" aria-label="<?= $contents['total'] ?> contenus au total">
            <div class="stat-card__icon stat-card__icon--total" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="2" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                    <rect x="11" y="2" width="7" height="4.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                    <rect x="11" y="8.5" width="7" height="9.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                    <rect x="2" y="13" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                </svg>
            </div>
            <div class="stat-card__body">
                <span class="stat-card__value"><?= number_format($contents['total']) ?></span>
                <span class="stat-card__label">Contenus total</span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Utilisateurs -->
        <?php if (Acl::can($userRole, 'user.manage')): ?>
        <a href="/admin/users" class="stat-card stat-card--link" aria-label="<?= $users['total'] ?> utilisateurs">
            <div class="stat-card__icon stat-card__icon--users" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="7.5" cy="6" r="3" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M1 17c0-3.314 2.91-6 6.5-6s6.5 2.686 6.5 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M15 8.5a2.5 2.5 0 100-5M19 17c0-2.761-1.79-5-4-5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="stat-card__body">
                <span class="stat-card__value"><?= number_format($users['total']) ?></span>
                <span class="stat-card__label">Utilisateurs</span>
            </div>
            <span class="stat-card__arrow" aria-hidden="true">→</span>
        </a>
        <?php endif; ?>

        <!-- Médias -->
        <?php if (Acl::can($userRole, 'media.upload')): ?>
        <a href="/admin/media" class="stat-card stat-card--link" aria-label="<?= $media ?> médias">
            <div class="stat-card__icon stat-card__icon--media" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="4" width="16" height="12" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="6.5" cy="8" r="1.5" stroke="currentColor" stroke-width="1.25"/>
                    <path d="M2 13l4-4 3 3 3-3.5L18 13" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="stat-card__body">
                <span class="stat-card__value"><?= number_format($media) ?></span>
                <span class="stat-card__label">Médias</span>
            </div>
            <span class="stat-card__arrow" aria-hidden="true">→</span>
        </a>
        <?php endif; ?>

    </div>
</section>


<?php if (Acl::can($userRole, 'user.manage') && $users['total'] > 0): ?>
<section class="dashboard-section" aria-labelledby="users-breakdown-heading">
    <h3 class="dashboard-section__title" id="users-breakdown-heading">Répartition des utilisateurs</h3>
    <div class="role-breakdown">
        <?php
        $roleBreakdown = [
            'admin'  => ['label' => 'Admins',    'class' => 'badge--admin',  'count' => $users['admin']  ?? 0],
            'editor' => ['label' => 'Éditeurs',  'class' => 'badge--editor', 'count' => $users['editor'] ?? 0],
            'author' => ['label' => 'Auteurs',   'class' => 'badge--author', 'count' => $users['author'] ?? 0],
            'reader' => ['label' => 'Lecteurs',  'class' => 'badge--reader', 'count' => $users['reader'] ?? 0],
        ];
        foreach ($roleBreakdown as $item): ?>
        <div class="role-breakdown__item">
            <span class="badge <?= $item['class'] ?>"><?= $item['label'] ?></span>
            <span class="role-breakdown__count"><?= number_format($item['count']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>


<section class="dashboard-section" aria-labelledby="shortcuts-heading">
    <h3 class="dashboard-section__title" id="shortcuts-heading">Accès rapides</h3>

    <div class="shortcuts-grid">

        <?php if (Acl::can($userRole, 'content.create')): ?>
        <a href="/admin/contents/new" class="shortcut-card">
            <span class="shortcut-card__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="shortcut-card__label">Créer un contenu</span>
            <span class="shortcut-card__desc">Rédiger et publier un nouvel article</span>
        </a>
        <?php endif; ?>

        <?php if (Acl::can($userRole, 'user.manage')): ?>
        <a href="/admin/users" class="shortcut-card">
            <span class="shortcut-card__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.75"/>
                    <path d="M2 21c0-4.418 3.134-8 7-8s7 3.582 7 8" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                    <path d="M19 8v6M22 11h-6" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="shortcut-card__label">Gérer les utilisateurs</span>
            <span class="shortcut-card__desc">Rôles, accès et comptes membres</span>
        </a>
        <?php endif; ?>

        <?php if (Acl::can($userRole, 'media.upload')): ?>
        <a href="/admin/media" class="shortcut-card">
            <span class="shortcut-card__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.75"/>
                    <circle cx="8" cy="10" r="2" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M3 17l5-5 3.5 3.5 4-5L21 17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="shortcut-card__label">Médiathèque</span>
            <span class="shortcut-card__desc">Images, documents et fichiers uploadés</span>
        </a>
        <?php endif; ?>

        <?php if (Acl::can($userRole, 'content.read')): ?>
        <a href="/admin/contents" class="shortcut-card">
            <span class="shortcut-card__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 5h18M3 10h14M3 15h16M3 20h10" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="shortcut-card__label">Tous les contenus</span>
            <span class="shortcut-card__desc">Parcourir et filtrer les contenus existants</span>
        </a>
        <?php endif; ?>

        <?php if (Acl::can($userRole, 'settings.manage')): ?>
        <a href="/admin/settings" class="shortcut-card">
            <span class="shortcut-card__icon" aria-hidden="true">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.75"/>
                    <path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.93 4.93l2.12 2.12M16.95 16.95l2.12 2.12M4.93 19.07l2.12-2.12M16.95 7.05l2.12-2.12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </span>
            <span class="shortcut-card__label">Paramètres</span>
            <span class="shortcut-card__desc">Configuration générale du CMS</span>
        </a>
        <?php endif; ?>

    </div>
</section>
