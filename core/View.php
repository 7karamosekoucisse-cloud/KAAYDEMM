<?php
declare(strict_types=1);
namespace Core;

final class View
{
    private static string $viewPath = '';
    private static array  $shared   = [];

    public static function setPath(string $path): void { self::$viewPath = rtrim($path, '/'); }

    public static function share(string $key, mixed $value): void { self::$shared[$key] = $value; }

    public static function render(string $view, array $data = []): string
    {
        $file = self::$viewPath . '/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: $file");
        }
        $data = array_merge(self::$shared, $data);
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return ob_get_clean();
    }

    public static function make(string $view, array $data = [], string $layout = 'layouts.main'): string
    {
        $content = self::render($view, $data);
        if ($layout) {
            $data['content'] = $content;
            return self::render($layout, $data);
        }
        return $content;
    }
}
