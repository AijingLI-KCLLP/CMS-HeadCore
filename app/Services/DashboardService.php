<?php

namespace App\Services;

use App\Repositories\DashboardRepository;
use Core\Services\AbstractService;

class DashboardService extends AbstractService
{
    private DashboardRepository $dashboardRepository;

    public function __construct()
    {
        $this->dashboardRepository = new DashboardRepository();
        parent::__construct($this->dashboardRepository);
    }

    public function getStats(): array
    {
        return [
            'contents' => $this->dashboardRepository->countContentsByStatus(),
            'users'    => $this->dashboardRepository->countUsersByRole(),
            'media'    => $this->dashboardRepository->countMedia(),
        ];
    }
}
