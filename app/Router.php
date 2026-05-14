<?php

declare(strict_types=1);

namespace App;

use App\Helpers\View;
use Closure;

/**
 * Roteador HTTP minimalista com suporte a parâmetros dinâmicos `{nome}`.
 *
 * Percorre a lista de rotas registradas, faz casamento por método e URI e
 * executa o handler (array classe+método ou Closure) com middleware opcional.
 */
final class Router
{
    /** @var list<array{methods:list<string>,pattern:string,regex:string,params:list<string>,handler:mixed,middleware?:list<string>}> */
    private array $routes;

    /**
     * @param list<array<string,mixed>> $routes Definição vinda de `config/routes.php`
     */
    public function __construct(array $routes)
    {
        $this->routes = $this->compile($routes);
    }

    /**
     * Despacha a requisição atual para o primeiro handler compatível.
     *
     * @param string $method Verbo HTTP normalizado em maiúsculas
     * @param string $uri URI completa (incluindo possível query string, que será removida)
     */
    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $rawPath = parse_url($uri, PHP_URL_PATH);
        $path = is_string($rawPath) ? $rawPath : '/';
        if ($path === '') {
            $path = '/';
        }
        $path = rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

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
                $controller->{$action}(...array_values($args));
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

    /**
     * Transforma padrões `/foo/{id}` em regex nomeados.
     *
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
     * Executa middleware pelo nome curto registrado no mapa interno.
     *
     * @param array<string, mixed> $routeArgs Argumentos extraídos da URI
     * @return bool|null Retorna false para interromper a cadeia
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
