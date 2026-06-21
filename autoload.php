<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $map = [
        'App\\'  => __DIR__ . '/app/',
        'Core\\' => __DIR__ . '/core/',
    ];
    foreach ($map as $prefix => $base) {
        if (str_starts_with($class, $prefix)) {
            $file = $base . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require_once $file;
                return;
            }
        }
    }
});
