<?php

namespace SentientCoach\Utils;

class Router
{
    private array $routes = [];
    
    public function get(string $path, callable $callback): void
    {
        $this->addRoute('GET', $path, $callback);
    }
    
    public function post(string $path, callable $callback): void
    {
        $this->addRoute('POST', $path, $callback);
    }
    
    private function addRoute(string $method, string $path, callable $callback): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback
        ];
    }
    
    public function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestUri)) {
                $params = $this->extractParams($route['path'], $requestUri);
                
                // Handle both array [object, method] and callable formats
                if (is_array($route['callback']) && count($route['callback']) === 2) {
                    $controller = $route['callback'][0];
                    $method = $route['callback'][1];
                    
                    if (empty($params)) {
                        $controller->$method();
                    } else {
                        call_user_func_array([$controller, $method], $params);
                    }
                } else {
                    call_user_func_array($route['callback'], $params);
                }
                return;
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }
    
    private function matchPath(string $routePath, string $requestUri): bool
    {
        $routePath = rtrim($routePath, '/');
        $requestUri = rtrim($requestUri, '/');
        
        if ($routePath === $requestUri) {
            return true;
        }
        
        // Handle dynamic parameters like /api/export/{format}
        $routePattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';
        
        return preg_match($routePattern, $requestUri);
    }
    
    private function extractParams(string $routePath, string $requestUri): array
    {
        $routePath = rtrim($routePath, '/');
        $requestUri = rtrim($requestUri, '/');
        
        $routePattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $routePattern = '#^' . $routePattern . '$#';
        
        if (preg_match($routePattern, $requestUri, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }
        
        return [];
    }
} 