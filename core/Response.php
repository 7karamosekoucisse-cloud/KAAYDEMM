<?php
declare(strict_types=1);
namespace Core;

final class Response
{
    private int    $status;
    private string $body;
    private array  $headers;

    public function __construct(string $body = '', int $status = 200, array $headers = [])
    {
        $this->body    = $body;
        $this->status  = $status;
        $this->headers = $headers;
    }

    public static function html(string $body, int $status = 200): self
    {
        return new self($body, $status, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public static function json(mixed $data, int $status = 200): self
    {
        return new self(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }

    /**
     * Redirect vers un chemin relatif (/login, /dashboard...).
     * Préfixe automatiquement avec le sous-dossier XAMPP si nécessaire.
     */
    public static function redirect(string $path, int $status = 302): self
    {
        // Calculer le base path dynamiquement (ex: /kaaydem/public)
        $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $base   = rtrim(dirname($script), '/');

        // Si path commence déjà par http:// ou https://, on le laisse tel quel
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $url = $path;
        } else {
            // Construire l'URL complète : base + /path
            $url = $base . '/' . ltrim($path, '/');
        }

        return new self('', $status, ['Location' => $url]);
    }

    public static function notFound(string $msg = 'Not Found'): self
    {
        return new self($msg, 404, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public static function forbidden(string $msg = 'Forbidden'): self
    {
        return new self($msg, 403, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function send(): void
    {
        if (headers_sent($file, $line)) {
            // Headers déjà envoyés : loguer et continuer
            error_log("Headers already sent in $file on line $line");
        } else {
            http_response_code($this->status);
            foreach ($this->headers as $k => $v) {
                header("$k: $v");
            }
        }
        echo $this->body;
    }

    public function getStatus(): int   { return $this->status; }
    public function getBody(): string  { return $this->body; }
}
