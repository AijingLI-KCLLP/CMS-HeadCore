<?php

namespace Core\Http;

class ApiResponse
{
    public static function success(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status, [
            'Content-Type' => 'application/json',
        ]);
    }

    public static function error(string $message, int $code): Response
    {
        return Response::json([
            'error'   => true,
            'message' => $message,
            'code'    => $code,
        ], $code);
    }

    public static function paginated(array $items, int $total, int $page, int $limit): Response
    {
        return self::success([
            'data' => $items,
            'meta' => [
                'page'        => $page,
                'limit'       => $limit,
                'total'       => $total,
                'total_pages' => (int) ceil($total / max($limit, 1)),
            ],
        ]);
    }
}