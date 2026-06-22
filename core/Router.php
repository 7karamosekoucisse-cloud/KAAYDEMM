<?php
declare(strict_types=1);
namespace Core;

use App\Exceptions\{NotFoundException, AuthorizationException};

final class Router
{
    private array $routes = [];
    private array $namedRoutes = [];

    public function __construct(private Container $container) {}

    public function get(string $path, array $handler, array $middlewares = [], string $name = ''): void
    {
        $this->add('GET', $path, $handler, $middlewares, $name);
    }

    public function post(string $path, array $handler, array $middlewares = [], string $name = ''): void
    {
        $this->add('POST', $path, $handler, $middlewares, $name);
    }

    private function add(string $method, string $path, array $handler, array $mws, string $name): void
    {
        $pattern = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path) . '$#';
        $route = compact('pattern', 'handler', 'method') + ['middlewares' => $mws];
        $this->routes[] = $route;
        if ($name) {
            $this->namedRoutes[$name] = $path;
        }
    }

    public function url(string $name, array $params = []): string
    {
        $path = $this->namedRoutes[$name] ?? "/$name";
        foreach ($params as $k => $v) {
            $path = str_replace('{' . $k . '}', (string)$v, $path);
        }
        return $path;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path   = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;
            if (!preg_match($route['pattern'], $path, $matches)) continue;

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            foreach ($route['middlewares'] as $mwClass) {
                $mw = $this->container->get($mwClass);
                $result = $mw->handle($request);
                if ($result instanceof Response) return $result;
            }

            [$controllerClass, $action] = $route['handler'];
            $controller = $this->container->get($controllerClass);
            return $controller->$action($request, ...array_values($params));
        }

        throw new NotFoundException("Route non trouvée : $method $path");
    }
}
