<?php

namespace App\Controllers;

use App\Services\DashboardService;
use App\Services\UserService;
use Core\Auth\Auth;
use Core\Controllers\AbstractController;
use Core\Http\Request;
use Core\Http\Response;

class DashboardController extends AbstractController
{
    private DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    public function process(Request $request): Response
    {
        // Guard : seuls les utilisateurs connectés peuvent accéder au dashboard
        Auth::guard();

        $role      = Auth::role() ?? '';
        $userId    = Auth::id();
        $userEmail = $this->resolveEmail($userId);

        $stats = $this->dashboardService->getStats();

        // Rendu de la vue partielle (le contenu injecté dans le layout)
        $content = $this->renderPartial('admin/dashboard', [
            'stats'     => $stats,
            'userRole'  => $role,
            'userEmail' => $userEmail,
        ]);

        // Rendu du layout principal avec les données de session
        return $this->renderLayout('layouts/backoffice', [
            'pageTitle'      => 'Tableau de bord',
            'currentSection' => '',
            'userEmail'      => $userEmail,
            'userRole'       => $role,
            'content'        => $content,
        ]);
    }


    private function resolveEmail(int|string|null $userId): string
    {
        if ($userId === null) {
            return '';
        }

        // On réutilise UserService pour éviter la duplication de logique
        $userService = new UserService();
        $user        = $userService->getUserById((int) $userId);

        return $user?->getEmail() ?? '';
    }


    private function renderPartial(string $template, array $data = []): string
    {
        extract($data);
        ob_start();
        require __DIR__ . "/../../resources/views/{$template}.php";
        return ob_get_clean();
    }

    private function renderLayout(string $template, array $data = []): Response
    {
        extract($data);
        ob_start();
        require __DIR__ . "/../../resources/views/{$template}.php";
        $html = ob_get_clean();

        return new Response($html, 200, ['Content-Type' => 'text/html']);
    }
}
