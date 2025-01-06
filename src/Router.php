<?php

namespace Arsen\ExrPhpRest;

class Router
{
    private $routes = [];

    public function addRoute($method, $path, $handler)
    {
        $path = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $path);
        $path = str_replace('/', '\/', $path);

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => '/^' . $path . '$/',
            'handler' => $handler
        ];
    }

    public function dispatch($requestedMethod, $requestedUri)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($requestedMethod) && preg_match($route['path'], $requestedUri, $matches)) {
                array_shift($matches);
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found.']);
    }
}
