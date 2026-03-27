<?php

namespace App\Controllers;

use App\Repositories\ContentRepository;
use App\Services\UserService;
use App\Services\WorkflowService;
use Core\Auth\Acl;
use Core\Auth\Auth;
use Core\Controllers\AbstractController;
use Core\Http\Request;
use Core\Http\Response;

class AdminContentController extends AbstractController
{
    private const PER_PAGE = 20;

    private ContentRepository $contentRepository;
    private WorkflowService   $workflowService;
    private UserService       $userService;

    public function __construct()
    {
        $this->contentRepository = new ContentRepository();
        $this->workflowService   = new WorkflowService();
        $this->userService       = new UserService();
    }

    public function process(Request $request): Response
    {
        Auth::guard();

        $role = Auth::role() ?? '';

        if (!Acl::can($role, 'content.read')) {
            return Response::error('Forbidden', 403);
        }

        $statusFilter = $request->getUrlParams()['status'] ?? '';
        $page         = max(1, (int) ($request->getUrlParams()['page'] ?? 1));
        $userId       = (int) Auth::id();
        $userEmail    = $this->userService->getUserById($userId)?->getEmail() ?? '';

        $all = $statusFilter !== ''
            ? $this->contentRepository->findByStatus($statusFilter)
            : $this->contentRepository->findAll();

        $total      = count($all);
        $totalPages = (int) ceil($total / self::PER_PAGE);
        $slice      = array_slice($all, ($page - 1) * self::PER_PAGE, self::PER_PAGE);

        $userCache = [];
        $items = array_map(function ($content) use (&$userCache) {
            $authorId = $content->getAuthorId();
            if (!isset($userCache[$authorId])) {
                $user = $this->userService->getUserById($authorId);
                $userCache[$authorId] = $user?->getEmail() ?? "#{$authorId}";
            }
            return ['entity' => $content, 'author_name' => $userCache[$authorId]];
        }, $slice);

        $transitionsFor = fn(string $status): array => $this->buildTransitionLabels(
            $this->workflowService->allowedTransitions($status),
            $role
        );

        $content = $this->renderPartial('admin/contents/list', [
            'items'          => $items,
            'total'          => $total,
            'totalPages'     => max(1, $totalPages),
            'page'           => $page,
            'statusFilter'   => $statusFilter,
            'userRole'       => $role,
            'userId'         => $userId,
            'transitionsFor' => $transitionsFor,
        ]);

        return $this->renderLayout('layouts/backoffice', [
            'pageTitle'      => 'Contenus',
            'currentSection' => 'contents',
            'userEmail'      => $userEmail,
            'userRole'       => $role,
            'content'        => $content,
        ]);
    }

    private function buildTransitionLabels(array $targets, string $role): array
    {
        $labels = [
            'draft'     => 'Repasser en brouillon',
            'review'    => 'Soumettre à relecture',
            'published' => 'Publier',
            'archived'  => 'Archiver',
        ];

        $result = [];
        foreach ($targets as $target) {
            $result[$target] = $labels[$target] ?? ucfirst($target);
        }

        return $result;
    }

    private function renderPartial(string $template, array $data = []): string
    {
        extract($data);
        ob_start();
        require __DIR__ . '/../../resources/views/' . $template . '.php';
        return ob_get_clean();
    }

    private function renderLayout(string $template, array $data = []): Response
    {
        extract($data);
        ob_start();
        require __DIR__ . '/../../resources/views/' . $template . '.php';
        $html = ob_get_clean();

        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
}