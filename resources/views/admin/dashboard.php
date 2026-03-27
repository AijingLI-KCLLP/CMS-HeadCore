<?php
$contentsStats = $stats['contents'] ?? [];
$usersStats    = $stats['users']    ?? [];
$mediaCount    = $stats['media']    ?? 0;

$totalContents  = array_sum($contentsStats);
$draftCount     = $contentsStats['draft']     ?? 0;
$publishedCount = $contentsStats['published'] ?? 0;
$archivedCount  = $contentsStats['archived']  ?? 0;
$totalUsers     = array_sum($usersStats);
?>

<div class="dashboard-welcome">
    <div>
        <p class="welcome__greeting">Bienvenue</p>
        <h2 class="welcome__name"><?= htmlspecialchars($userEmail) ?></h2>
        <div class="welcome__role">
            <span class="badge badge--<?= htmlspecialchars($userRole) ?>"><?= htmlspecialchars(ucfirst($userRole)) ?></span>
        </div>
    </div>
    <span class="welcome__date"><?= date('d/m/Y') ?></span>
</div>

<div class="dashboard-section">
    <p class="dashboard-section__title">Contenus</p>
    <div class="stats-grid">
        <div class="stat-card stat-card--total">
            <span class="stat-card__icon stat-card__icon--total">
                <svg width="18" height="18" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="1" y="1" width="6" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
                    <rect x="9" y="1" width="6" height="4" rx="1" stroke="currentColor" stroke-width="1.5"/>
                    <rect x="9" y="7" width="6" height="8" rx="1" stroke="currentColor" stroke-width="1.5"/>
                    <rect x="1" y="11" width="6" height="4" rx="1" stroke="currentColor" stroke-width="1.5"/>
                </svg>
            </span>
            <div class="stat-card__body">
                <span class="stat-card__value"><?= $totalContents ?></span>
                <span class="stat-card__label">Total</span>
            </div>
        </div>

        <a href="/admin/contents?status=draft" class="stat-card stat-card--link">
            <span class="stat-card__icon stat-card__icon--draft">
                <svg width="18" height="18" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 4h12M2 8h8M2 12h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </span>
            <div class="stat-card__body">
                <span class="stat-card__value"><?= $draftCount ?></span>
                <span class="stat-card__label">Brouillons</span>
            </div>
            <span class="stat-card__arrow">→</span>
        </a>

        <a href="/admin/contents?status=published" class="stat-card stat-card--link">
            <span class="stat-card__icon stat-card__icon--published">
                <svg width="18" height="18" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <div class="stat-card__body">
                <span class="stat-card__value"><?= $publishedCount ?></span>
                <span class="stat-card__label">Publiés</span>
            </div>
            <span class="stat-card__arrow">→</span>
        </a>

        <a href="/admin/contents?status=archived" class="stat-card stat-card--link">
            <span class="stat-card__icon stat-card__icon--archived">
                <svg width="18" height="18" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="1.5" y="3.5" width="13" height="2.5" rx="1" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M2.5 6v6a1 1 0 001 1h9a1 1 0 001-1V6" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M6 9h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </span>
            <div class="stat-card__body">
                <span class="stat-card__value"><?= $archivedCount ?></span>
                <span class="stat-card__label">Archivés</span>
            </div>
            <span class="stat-card__arrow">→</span>
        </a>
    </div>
</div>

<div class="dashboard-section">
    <p class="dashboard-section__title">Équipe & médias</p>
    <div class="stats-grid">
        <a href="/admin/users" class="stat-card stat-card--link">
            <span class="stat-card__icon stat-card__icon--users">
                <svg width="18" height="18" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="6" cy="5" r="3" stroke="currentColor" stroke-width="1.5"/>
                    <path d="M1 13.5C1 11.015 3.239 9 6 9s5 2.015 5 4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M11.5 7.5L13 9L15.5 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <div class="stat-card__body">
                <span class="stat-card__value"><?= $totalUsers ?></span>
                <span class="stat-card__label">Utilisateurs</span>
            </div>
            <span class="stat-card__arrow">→</span>
        </a>

        <a href="/admin/media" class="stat-card stat-card--link">
            <span class="stat-card__icon stat-card__icon--media">
                <svg width="18" height="18" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="1" y="3" width="14" height="10" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="5.5" cy="6.5" r="1.5" stroke="currentColor" stroke-width="1.25"/>
                    <path d="M1 10.5L4.5 7.5L7 10L10 7L15 11" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <div class="stat-card__body">
                <span class="stat-card__value"><?= $mediaCount ?></span>
                <span class="stat-card__label">Médias</span>
            </div>
            <span class="stat-card__arrow">→</span>
        </a>
    </div>
</div>

<?php if (!empty($usersStats)): ?>
<div class="dashboard-section">
    <p class="dashboard-section__title">Répartition des rôles</p>
    <div class="role-breakdown">
        <?php foreach ($usersStats as $role => $count): ?>
        <div class="role-breakdown__item">
            <span class="role-breakdown__count"><?= $count ?></span>
            <span class="badge badge--<?= htmlspecialchars($role) ?>"><?= htmlspecialchars(ucfirst($role)) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>