<?php

use Core\Auth\Acl;


$statusMeta = [
    'draft'     => ['label' => 'Brouillon',       'class' => 'badge--status-draft'],
    'review'    => ['label' => 'Prêt à relire',   'class' => 'badge--status-review'],
    'published' => ['label' => 'Publié',           'class' => 'badge--status-published'],
    'archived'  => ['label' => 'Archivé',          'class' => 'badge--status-archived'],
];

$filterOptions = [
    ''          => 'Tous les statuts',
    'draft'     => 'Brouillons',
    'review'    => 'Prêts à relire',
    'published' => 'Publiés',
    'archived'  => 'Archivés',
];

$buildUrl = static function (array $params) use ($statusFilter, $page): string {
    $base = ['status' => $statusFilter, 'page' => $page];
    $merged = array_merge($base, $params);
    $qs = http_build_query(array_filter(
        $merged,
        static fn($v) => $v !== '' && $v !== null
    ));
    return '/admin/contents' . ($qs !== '' ? '?' . $qs : '');
};

$formatDate = static function (?string $date): string {
    if ($date === null) {
        return '—';
    }
    $ts = strtotime($date);
    return $ts !== false ? date('d/m/Y', $ts) : '—';
};
?>

<section class="contents-page" aria-label="Liste des contenus">

    <!-- En-tête de section : titre + bouton Nouveau contenu  -->
    
    <div class="contents-page__toolbar">
        <div class="contents-page__toolbar-left">
            <p class="contents-page__count">
                <?php if ($total === 0): ?>
                    Aucun contenu
                <?php elseif ($total === 1): ?>
                    1 contenu
                <?php else: ?>
                    <?= $total ?> contenus
                <?php endif; ?>
                <?php if ($statusFilter !== '' && isset($filterOptions[$statusFilter])): ?>
                    <span class="contents-page__count-filter">
                        — filtre : <?= htmlspecialchars($filterOptions[$statusFilter]) ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>

        <div class="contents-page__toolbar-right">

            <!-- Filtre par statut -->
            <div class="contents-filter" role="group" aria-label="Filtrer par statut">
                <?php foreach ($filterOptions as $value => $label): ?>
                <?php
                    $isCurrent = ($statusFilter === $value);
                    $url       = $buildUrl(['status' => $value, 'page' => 1]);
                ?>
                <a href="<?= htmlspecialchars($url) ?>"
                   class="contents-filter__btn <?= $isCurrent ? 'contents-filter__btn--active' : '' ?>"
                   <?= $isCurrent ? 'aria-current="true"' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Bouton Nouveau contenu  -->
            <?php if (Acl::can($userRole, 'content.create')): ?>
            <a href="/admin/contents/new" class="btn btn--primary">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                     xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M8 3v10M3 8h10"
                          stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                </svg>
                Nouveau contenu
            </a>
            <?php endif; ?>

        </div>
    </div>

    <!-- Tableau  -->
    <?php if (empty($items)): ?>

    <div class="contents-empty" role="status">
        <span class="contents-empty__icon" aria-hidden="true">
            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="8" y="5" width="24" height="30" rx="3" stroke="currentColor" stroke-width="1.75"/>
                <path d="M14 13h12M14 19h8M14 25h10" stroke="currentColor"
                      stroke-width="1.5" stroke-linecap="round"/>
            </svg>
        </span>
        <p class="contents-empty__label">
            <?= $statusFilter !== ''
                ? 'Aucun contenu avec ce statut.'
                : 'Aucun contenu pour le moment.' ?>
        </p>
        <?php if (Acl::can($userRole, 'content.create')): ?>
        <a href="/admin/contents/new" class="btn btn--ghost">Créer le premier contenu</a>
        <?php endif; ?>
    </div>

    <?php else: ?>

    <div class="table-wrapper" role="region" aria-label="Contenus" tabindex="0">
        <table class="table contents-table" aria-label="Liste des contenus">
            <thead>
                <tr>
                    <th scope="col">Titre</th>
                    <th scope="col">Auteur</th>
                    <th scope="col">Statut</th>
                    <th scope="col">Créé le</th>
                    <th scope="col" class="col-actions">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $row):
                /** @var \App\Entities\Content $content */
                $content    = $row['entity'];
                $authorName = $row['author_name'];
                $status     = $content->getStatus();
                $meta       = $statusMeta[$status] ?? ['label' => $status, 'class' => ''];
                $isOwn      = $content->getAuthorId() === $userId;
                $transitions = $transitionsFor($status);
            ?>
            <tr class="contents-row" data-id="<?= $content->getId() ?>">

                <!-- Titre -->
                <td class="contents-row__title">
                    <span class="contents-row__title-text"
                          title="<?= htmlspecialchars($content->getTitle()) ?>">
                        <?= htmlspecialchars($content->getTitle()) ?>
                    </span>
                    <span class="contents-row__slug">/<?= htmlspecialchars($content->getSlug()) ?></span>
                </td>

                <!-- Auteur -->
                <td class="contents-row__author">
                    <?= htmlspecialchars($authorName) ?>
                </td>

                <!-- Statut -->
                <td class="contents-row__status">
                    <span class="badge <?= $meta['class'] ?>"><?= htmlspecialchars($meta['label']) ?></span>
                </td>

                <!-- Date de création -->
                <td class="contents-row__date">
                    <time datetime="<?= htmlspecialchars($content->getCreatedAt()) ?>">
                        <?= $formatDate($content->getCreatedAt()) ?>
                    </time>
                </td>

                <!-- Actions -->
                <td class="contents-row__actions">
                    <div class="row-actions" role="group"
                         aria-label="Actions pour « <?= htmlspecialchars($content->getTitle()) ?> »">

                        <!-- Éditer : edit.own si auteur, edit.any sinon -->
                        <?php
                        $canEdit = ($isOwn && Acl::can($userRole, 'content.edit.own'))
                                || Acl::can($userRole, 'content.edit.any');
                        ?>
                        <?php if ($canEdit): ?>
                        <a href="/admin/contents/<?= $content->getId() ?>/edit"
                           class="btn btn--ghost btn--sm"
                           title="Éditer ce contenu">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                 xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M9.5 1.5a1.414 1.414 0 012 2L4 11H1.5V8.5L9.5 1.5z"
                                      stroke="currentColor" stroke-width="1.4"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Éditer</span>
                        </a>
                        <?php endif; ?>

                        <!-- Changer statut  -->
                        <?php if (!empty($transitions)): ?>
                        <div class="row-actions__dropdown" data-dropdown>
                            <button type="button"
                                    class="btn btn--ghost btn--sm"
                                    data-dropdown-toggle
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    title="Changer le statut">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                     xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M7 1v12M1 7h12" stroke="currentColor"
                                          stroke-width="1.4" stroke-linecap="round"/>
                                </svg>
                                <span>Statut</span>
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none"
                                     xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M2 3.5L5 6.5L8 3.5" stroke="currentColor"
                                          stroke-width="1.4" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <?php foreach ($transitions as $target => $label): ?>
                                <li role="none">
                                    <form method="POST"
                                          action="/admin/contents/<?= $content->getId() ?>/status"
                                          class="dropdown-menu__form">
                                        <input type="hidden" name="status"
                                               value="<?= htmlspecialchars($target) ?>">
                                        <button type="submit"
                                                class="dropdown-menu__item"
                                                role="menuitem">
                                            <?= htmlspecialchars($label) ?>
                                        </button>
                                    </form>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Supprimer -->
                        <?php
                        $canDelete = Acl::can($userRole, 'content.delete')
                                  || ($isOwn && Acl::can($userRole, 'content.edit.own'));
                        ?>
                        <?php if ($canDelete): ?>
                        <form method="POST"
                              action="/admin/contents/<?= $content->getId() ?>/delete"
                              class="row-actions__delete-form"
                              onsubmit="return confirm('Supprimer « <?= addslashes(htmlspecialchars($content->getTitle())) ?> » ?\nCette action est irréversible.')">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit"
                                    class="btn btn--danger btn--sm"
                                    title="Supprimer ce contenu">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"
                                     xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M2 4h10M5 4V2.5h4V4M5.5 6.5v4M8.5 6.5v4M3 4l.75 7.5a1 1 0 001 .875h4.5a1 1 0 001-.875L11 4"
                                          stroke="currentColor" stroke-width="1.4"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span class="sr-only">Supprimer</span>
                            </button>
                        </form>
                        <?php endif; ?>

                    </div>
                </td>

            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Pagination">

        <!-- Précédent -->
        <?php if ($page > 1): ?>
        <a href="<?= htmlspecialchars($buildUrl(['page' => $page - 1])) ?>"
           class="pagination__btn" rel="prev" aria-label="Page précédente">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                 xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M10 3L5.5 8L10 13" stroke="currentColor"
                      stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <?php else: ?>
        <span class="pagination__btn pagination__btn--disabled" aria-disabled="true">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                 xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M10 3L5.5 8L10 13" stroke="currentColor"
                      stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <?php endif; ?>

        <!-- Numéros de pages -->
        <span class="pagination__pages">
            <?php
            // Afficher au maximum 7 numéros 
            $window = 2;
            $pagesShown = [];
            for ($p = 1; $p <= $totalPages; $p++) {
                if (
                    $p === 1
                    || $p === $totalPages
                    || ($p >= $page - $window && $p <= $page + $window)
                ) {
                    $pagesShown[] = $p;
                }
            }
            $prev = null;
            foreach ($pagesShown as $p):
                if ($prev !== null && $p > $prev + 1): ?>
                <span class="pagination__ellipsis" aria-hidden="true">…</span>
                <?php endif; ?>
                <?php if ($p === $page): ?>
                <span class="pagination__page pagination__page--current"
                      aria-current="page"><?= $p ?></span>
                <?php else: ?>
                <a href="<?= htmlspecialchars($buildUrl(['page' => $p])) ?>"
                   class="pagination__page"
                   aria-label="Page <?= $p ?>"><?= $p ?></a>
                <?php endif; ?>
                <?php $prev = $p;
            endforeach; ?>
        </span>

        <!-- Suivant -->
        <?php if ($page < $totalPages): ?>
        <a href="<?= htmlspecialchars($buildUrl(['page' => $page + 1])) ?>"
           class="pagination__btn" rel="next" aria-label="Page suivante">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                 xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M6 3L10.5 8L6 13" stroke="currentColor"
                      stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </a>
        <?php else: ?>
        <span class="pagination__btn pagination__btn--disabled" aria-disabled="true">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                 xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M6 3L10.5 8L6 13" stroke="currentColor"
                      stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </span>
        <?php endif; ?>

    </nav>
    <?php endif; ?>

    <?php endif; ?>

</section>

<!-- Gestion dropdown statut -->

<script>
(function () {
    'use strict';

    function closeAll() {
        document.querySelectorAll('[data-dropdown]').forEach(function (d) {
            d.querySelector('[data-dropdown-toggle]').setAttribute('aria-expanded', 'false');
            d.classList.remove('is-open');
        });
    }

    document.addEventListener('click', function (e) {
        var toggle = e.target.closest('[data-dropdown-toggle]');
        if (toggle) {
            var dropdown = toggle.closest('[data-dropdown]');
            var isOpen   = dropdown.classList.contains('is-open');
            closeAll();
            if (!isOpen) {
                dropdown.classList.add('is-open');
                toggle.setAttribute('aria-expanded', 'true');
            }
            e.stopPropagation();
            return;
        }
        closeAll();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAll();
        }
    });
})();
</script>
