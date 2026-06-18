<?php

declare(strict_types=1);

namespace App;

use App\Helpers\Csrf;
use App\Helpers\Logger;
use App\Helpers\View;
use Closure;
use Throwable;

/**
 * Roteador HTTP minimalista com suporte a parâmetros dinâmicos `{nome}`.
 */
final class Router
{
    /** @var list<string> */
    private array $csrfExempt;

    /** @var list<array{methods:list<string>,pattern:string,regex:string,params:list<string>,handler:mixed,middleware?:list<string>}> */
    private array $routes;

    /**
     * @param list<array<string,mixed>> $routes Definição vinda de `config/routes.php`
     */
    public function __construct(array $routes)
    {
        $this->routes = $this->compile($routes);
        $this->csrfExempt = require BASE_PATH . '/config/csrf.php';
    }

    public function dispatch(string $method, string $uri): void
    {
        try {
            $this->dispatchInternal($method, $uri);
        } catch (Throwable $e) {
            Logger::log('error', 'Erro no roteador', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    private function dispatchInternal(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = $this->normalizePath($uri);

        foreach ($this->routes as $route) {
            if (!in_array($method, $route['methods'], true)) {
                continue;
            }

            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            $args = [];
            foreach ($route['params'] as $p) {
                $args[$p] = $matches[$p] ?? null;
            }

            if ($method === 'POST' && !$this->isCsrfExempt($path) && !Csrf::validate()) {
                http_response_code(419);
                if (str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['ok' => false, 'error' => 'csrf'], JSON_THROW_ON_ERROR);
                } else {
                    $_SESSION['flash_error'] = 'Sessão expirada. Atualize a página e tente novamente.';
                    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
                }

                return;
            }

            if (!empty($route['middleware'])) {
                foreach ($route['middleware'] as $mw) {
                    $ok = $this->runMiddleware($mw, $args);
                    if ($ok === false) {
                        return;
                    }
                }
            }

            $handler = $route['handler'];
            if (is_array($handler) && count($handler) === 2) {
                [$class, $action] = $handler;
                $controller = new $class();
                $controller->{$action}(...$this->coerceHandlerArgs($class, (string) $action, $args));

                return;
            }

            if ($handler instanceof Closure) {
                $handler(...array_values($args));

                return;
            }
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Página não encontrada'], ['layout' => 'public']);
    }

    private function normalizePath(string $uri): string
    {
        $rawPath = parse_url($uri, PHP_URL_PATH);
        $path = is_string($rawPath) ? $rawPath : '/';
        if ($path === '') {
            $path = '/';
        }
        $path = rtrim($path, '/');

        return $path === '' ? '/' : $path;
    }

    private function isCsrfExempt(string $path): bool
    {
        foreach ($this->csrfExempt as $pattern) {
            if (preg_match($pattern, $path) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array<string,mixed>> $routes
     * @return list<array<string,mixed>>
     */
    private function compile(array $routes): array
    {
        $compiled = [];
        foreach ($routes as $r) {
            $methods = array_map('strtoupper', (array) ($r['methods'] ?? ['GET']));
            $pattern = (string) ($r['path'] ?? '/');
            if ($pattern === '') {
                $pattern = '/';
            }
            $pattern = rtrim($pattern, '/');
            if ($pattern === '') {
                $pattern = '/';
            }

            $paramNames = [];
            $regex = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', static function (array $m) use (&$paramNames): string {
                $paramNames[] = $m[1];

                return '(?P<' . $m[1] . '>[^/]+)';
            }, $pattern);

            $regex = '#^' . str_replace('/', '\/', $regex) . '$#';

            $compiled[] = [
                'methods' => $methods,
                'pattern' => $pattern,
                'regex' => $regex,
                'params' => $paramNames,
                'handler' => $r['handler'] ?? null,
                'middleware' => isset($r['middleware']) ? (array) $r['middleware'] : [],
            ];
        }

        return $compiled;
    }

    /**
     * Converte parâmetros da URL para os tipos declarados no controller (ex.: `{id}` → int).
     *
     * @param array<string, mixed> $args
     * @return list<mixed>
     */
    private function coerceHandlerArgs(string $class, string $action, array $args): array
    {
        if (!class_exists($class) || !method_exists($class, $action)) {
            return array_values($args);
        }

        try {
            $method = new \ReflectionMethod($class, $action);
        } catch (\ReflectionException) {
            return array_values($args);
        }

        $coerced = [];
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (!array_key_exists($name, $args)) {
                if ($param->isDefaultValueAvailable()) {
                    $coerced[] = $param->getDefaultValue();
                }

                continue;
            }

            $value = $args[$name];
            $type = $param->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $coerced[] = $value;
                continue;
            }

            $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : null;
            $coerced[] = match ($typeName) {
                'int' => is_int($value) ? $value : (int) filter_var($value, FILTER_VALIDATE_INT),
                'float' => is_float($value) ? $value : (float) filter_var($value, FILTER_VALIDATE_FLOAT),
                'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value,
                'string' => (string) $value,
                default => $value,
            };
        }

        return $coerced;
    }

    /**
     * @param array<string, mixed> $routeArgs
     */
    private function runMiddleware(string $name, array $routeArgs): ?bool
    {
        return match ($name) {
            'customer_auth' => \App\Middleware\CustomerAuth::handle(),
            'admin_auth' => \App\Middleware\AdminAuth::handle('super_admin'),
            'operator_auth' => \App\Middleware\AdminAuth::handle('unit_operator'),
            'any_admin_auth' => \App\Middleware\AdminAuth::handleAny(),
            default => true,
        };
    }
}
