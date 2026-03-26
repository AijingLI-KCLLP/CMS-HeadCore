<?php

namespace App\Controllers;

use Core\Controllers\AbstractController;
use Core\Http\ApiResponse;
use Core\Http\Response;

abstract class ApiController extends AbstractController
{
    protected function success(mixed $data, int $status = 200): Response
    {
        return ApiResponse::success($data, $status);
    }

    protected function error(string $message, int $code): Response
    {
        return ApiResponse::error($message, $code);
    }

    protected function paginated(array $items, int $total, int $page, int $limit): Response
    {
        return ApiResponse::paginated($items, $total, $page, $limit);
    }
}