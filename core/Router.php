<?php

namespace Core;

class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, callable $handler): void { $this->routes['GET'][$path] = $handler; }
    public function post(string $path, callable $handler): void { $this->routes['POST'][$path] = $handler; }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        $base = base_path();
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $path = '/' . trim($uri, '/');
        if ($path === '') $path = '/';

        foreach ($this->routes[$method] ?? [] as $pattern => $handler) {
            $paramNames = [];
            $patternNormalized = '/' . trim($pattern, '/');
            if ($patternNormalized === '') $patternNormalized = '/';

            $regexBody = preg_replace_callback('/\{(\w+)\}/', function ($m) use (&$paramNames) {
                $paramNames[] = $m[1];
                return '([^/]+)';
            }, $patternNormalized);
            $regex = '#^' . $regexBody . '$#';

            if (preg_match($regex, $path, $matches)) {
                array_shift($matches);
                $params = array_combine($paramNames, $matches);
                call_user_func($handler, $params);
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . '/../app/Views/errors/404.php';
    }
}
