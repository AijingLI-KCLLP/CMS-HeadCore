<?php

namespace App\Services;

use App\Entities\Content;
use App\Repositories\ContentRepository;
use Core\Auth\Acl;

class WorkflowService
{
    private ContentRepository $contentRepository;

    public function __construct()
    {
        $this->contentRepository = new ContentRepository();
    }

    /**
     * Transition un content vers un nouveau statut.
     * Vérifie que la transition est valide dans la machine d'état
     * ET que le rôle courant possède la permission requise.
     *
     * @throws \RuntimeException 422 si transition invalide, 403 si rôle insuffisant
     */
    public function transition(Content $content, string $newStatus, string $role, int $updatedBy): void
    {
        $allowed = $this->allowedTransitions($content->getStatus());

        if (!in_array($newStatus, $allowed, true)) {
            throw new \RuntimeException(
                "Cannot transition from '{$content->getStatus()}' to '$newStatus'.",
                422
            );
        }

        $permission = $this->requiredPermission($content->getStatus(), $newStatus);

        if (!Acl::can($role, $permission)) {
            throw new \RuntimeException(
                "Role '$role' is not allowed to transition from '{$content->getStatus()}' to '$newStatus'.",
                403
            );
        }

        $content->setStatus($newStatus)
                ->setUpdatedBy($updatedBy)
                ->setUpdatedAt(date('Y-m-d H:i:s'));

        if ($newStatus === 'published') {
            $content->setPublishedAt(date('Y-m-d H:i:s'));
        }

        $this->contentRepository->update($content);
    }

    /**
     * Transitions valides depuis un statut donné (machine d'état pure, sans rôle).
     */
    public function allowedTransitions(string $current): array
    {
        return match ($current) {
            'draft'     => ['review'],
            'review'    => ['draft', 'published'],
            'published' => ['archived'],
            'archived'  => ['draft'],
            default     => [],
        };
    }

    /**
     * Permission ACL requise pour chaque transition.
     */
    private function requiredPermission(string $from, string $to): string
    {
        return match (true) {
            $from === 'draft'     && $to === 'review'    => 'content.create',
            $from === 'review'    && $to === 'draft'     => 'content.edit.any',
            $from === 'review'    && $to === 'published' => 'content.publish',
            $from === 'published' && $to === 'archived'  => 'content.archive',
            $from === 'archived'  && $to === 'draft'     => 'content.restore',
            default => throw new \RuntimeException("Unknown transition '$from' → '$to'.", 422),
        };
    }
}
