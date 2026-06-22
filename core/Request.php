<?php
declare(strict_types=1);
namespace Core;

final class Request
{
    private array $get;
    private array $post;
    private array $files;
    private array $server;
    private array $cookies;

    public function __construct(
        array $get     = [],
        array $post    = [],
        array $files   = [],
        array $server  = [],
        array $cookies = []
    ) {
        $this->get     = $get;
        $this->post    = $post;
        $this->files   = $files;
        $this->server  = $server;
        $this->cookies = $cookies;
    }

    public static function fromGlobals(): self
    {
        return new self($_GET, $_POST, $_FILES, $_SERVER, $_COOKIE);
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri  = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Supprimer le préfixe du sous-dossier (ex: /kaaydem/public)
        // SCRIPT_NAME = /kaaydem/public/index.php
        $script = $this->server['SCRIPT_NAME'] ?? '/index.php';
        $base   = rtrim(dirname($script), '/');  // /kaaydem/public

        if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        $path = '/' . ltrim($path, '/');
        return $path === '' ? '/' : $path;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function header(string $key): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$key] ?? null;
    }

    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR']
            ?? $this->server['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    public function isPost(): bool    { return $this->method() === 'POST'; }
    public function isGet(): bool     { return $this->method() === 'GET'; }
    public function isAjax(): bool    { return $this->header('X-Requested-With') === 'XMLHttpRequest'; }
    public function isJson(): bool    { return str_contains($this->header('Accept') ?? '', 'application/json'); }
    public function wantsJson(): bool { return $this->isJson() || str_starts_with($this->path(), '/api/'); }

    public function getServer(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }
}
