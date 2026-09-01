<?php

namespace App\Core;

/**
 * Minimal front-controller router. Routes are registered as:
 *   $router->get('/lop/{id}', [LopController::class, 'show']);
 * {param} segments are captured and passed as positional args to the handler,
 * after Request $request (which is always the first arg).
 */
class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, array $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, array $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $path);
        $this->routes[] = [
            'method' => $method,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches); // drop full match
                [$class, $action] = $route['handler'];
                $controller = new $class();

                try {
                    return $controller->$action($request, ...$matches);
                } catch (\App\Exceptions\BusinessRuleException $e) {
                    return Response::json([
                        'success' => false,
                        'error' => ['code' => $e->errorCode(), 'message' => $e->getMessage()],
                    ], 422);
                } catch (\App\Exceptions\ForbiddenException $e) {
                    return Response::json([
                        'success' => false,
                        'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage()],
                    ], 403);
                }
            }
        }

        return Response::json([
            'success' => false,
            'error' => ['code' => 'NOT_FOUND', 'message' => 'Route not found'],
        ], 404);
    }
}
