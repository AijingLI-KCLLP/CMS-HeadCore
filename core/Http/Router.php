<?php

namespace Core\Http;

use Core\Config\Config;
use Core\Controllers\AbstractController;


class Router {

    const string CONTROLLER_NAMESPACE_PREFIX = "App\\Controllers\\";

    public static function route(Request $request): Response {
        $routes = Config::get('routes');

        foreach($routes as $route) {
            if(self::checkMethod($request, $route) === false || self::checkUri($request, $route) === false) {
                continue;
            }

            $controller = self::getControllerInstance($route['controller']);
            return $controller->process($request);
        }

        throw new \Exception('Route not found', 404);
    }


    private static function checkMethod(Request $request, array $route): bool {
        return $request->getMethod() === $route['method'];
    }

    private static function checkUri(Request $request, array $route): bool {
        $requestUriParts = self::getUrlParts($request->getPath());
        $routePathParts = self::getUrlParts($route['path']);

        if(self::checkUrlPartsNumberMatches($requestUriParts, $routePathParts) === false) {
            return false;
        }

        foreach($routePathParts as $key => $part) {
            if(self::isUrlPartSlug($part) === false) {
                if($part !== $requestUriParts[$key]) {
                    return false;
                }
            }else{
                $request->addSlug(self::slugName($part), $requestUriParts[$key]);
            }
        }

        return true;
    }
    
    private static function getControllerInstance(string $controller): AbstractController {
        $controllerClass = self::CONTROLLER_NAMESPACE_PREFIX . $controller;

        if(class_exists($controllerClass) === false) {
            throw new \Exception('Route not found', 404);
        }

        $controllerInstance = new $controllerClass();

        if(is_subclass_of($controllerInstance, AbstractController::class)=== false){
            throw new \Exception('Route not found', 404);
        }
        
        return $controllerInstance;
    }

    private static function getUrlParts(string $url): array {
        return explode('/', trim($url, '/'));
    }

    private static function checkUrlPartsNumberMatches(array $requestUriParts, array $routePathParts): bool {
        return count($requestUriParts) === count($routePathParts);
    }

    private static function isUrlPartSlug(string $part): bool {
        return preg_match('/^\{[a-zA-Z_][a-zA-Z0-9_]*\}$/', $part) === 1;
    }

    private static function slugName(string $part): string {
        return trim($part, '{}');
    }

}
