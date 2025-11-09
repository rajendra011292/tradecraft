<?php
namespace App\Core;

class Router
{
    private App $app;
    private array $routes = [];
    private array $groupMiddleware = [];
    private string $groupPrefix = '';

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function get(string $path, array $action, array $middleware = []): void
    {
        $this->add('GET', $path, $action, $middleware);
    }

    public function post(string $path, array $action, array $middleware = []): void
    {
        $this->add('POST', $path, $action, $middleware);
    }

    public function group(string $prefix, array $middleware, callable $callback): void
    {
        $previousPrefix = $this->groupPrefix;
        $previousMw = $this->groupMiddleware;
        $this->groupPrefix .= $prefix;
        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        $callback($this);
        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMw;
    }

    private function add(string $method, string $path, array $action, array $middleware): void
    {
        $path = $this->groupPrefix . $path;
        $this->routes[] = compact('method','path','action') + ['middleware' => array_merge($this->groupMiddleware, $middleware)];
    }

   public function dispatch(): void
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    foreach ($this->routes as $route) {
        // Replace {param} with regex group and escape slashes properly
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route['path']);
        $pattern = '#^' . $pattern . '$#'; // use # delimiters, not / to avoid conflicts

        if ($method === $route['method'] && preg_match($pattern, $uri, $matches)) {
            array_shift($matches);
            $params = $matches;

            // Extract named parameters (e.g., {id})
            preg_match_all('/\{([^}]+)\}/', $route['path'], $names);
            $args = [];
            foreach ($names[1] ?? [] as $i => $name) {
                $args[$name] = $params[$i] ?? null;
            }

            // Run global middleware first
            foreach ($this->app->globalMiddleware as $mw) {
                (new $mw($this->app))->handle();
            }

            // Then route-specific middleware
            foreach ($route['middleware'] as $mw) {
                (new $mw($this->app))->handle();
            }

            // Invoke controller
            [$class, $methodName] = $route['action'];
            $controller = new $class($this->app);
            call_user_func_array([$controller, $methodName], $args);
            return;
        }
    }

    http_response_code(404);
    echo "Not Found";
}

}
