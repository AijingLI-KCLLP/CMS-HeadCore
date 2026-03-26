<?php

namespace App\Controllers;

use Core\Http\Request;
use Core\Http\Response;

class ApiPingController extends ApiController
{
    public function process(Request $request): Response
    {
        return $this->success(['status' => 'ok']);
    }
}