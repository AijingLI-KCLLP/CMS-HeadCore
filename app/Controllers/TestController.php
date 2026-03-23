<?php

namespace App\Controllers;

use Core\Controllers\AbstractController;
use Core\Http\Request;
use Core\Http\Response;

class TestController extends AbstractController {
    public function process(Request $request): Response {
        return Response::json([
            'message' => 'ok',
            'method'  => $request->getMethod(),
            'path'    => $request->getPath(),
            'id'      => $request->getSlug('id') ?: null,
            'expects_json' => $request->expectsJson(),
        ]);
    }
}