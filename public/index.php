<?php
declare(strict_types=1);

// Afficher les erreurs en dev (à désactiver en prod)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

define('ROOT', dirname(__DIR__));

// Autoload
require ROOT . '/autoload.php';
require ROOT . '/app/Helpers/functions.php';

// Config
$config = require ROOT . '/config/config.php';

// Timezone
date_default_timezone_set($config['app']['timezone']);

// Logger
\Core\Logger::setFile(ROOT . '/storage/logs/app.log');

// Session
\Core\Session::start($config['session']);

// Container DI
$container = require ROOT . '/config/container.php';

// Rendre le container accessible globalement (pour les helpers)
$GLOBALS['container'] = $container;

// View engine
\Core\View::setPath(ROOT . '/app/Views');
\Core\View::share('appName', $config['app']['name']);
\Core\View::share('appUrl',  $config['app']['url']);

// Router
$router = new \Core\Router($container);
require ROOT . '/routes/web.php';

// Request
$request = \Core\Request::fromGlobals();

// Remember-me auto-login
if (!\Core\Session::has('user_id') && $request->cookie('remember')) {
    try {
        $authService = $container->get(\App\Interfaces\AuthServiceInterface::class);
        if (method_exists($authService, 'loginWithRememberToken')) {
            $authService->loginWithRememberToken($request->cookie('remember'));
        }
    } catch (\Throwable) {}
}

// Dispatch
try {
    $response = $router->dispatch($request);
    $response->send();

} catch (\App\Exceptions\NotFoundException $e) {
    http_response_code(404);
    try {
        echo \Core\View::make('errors.404', ['title' => 'Page introuvable', 'message' => $e->getMessage()]);
    } catch (\Throwable) {
        echo '<h1>404 — Page introuvable</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
    }

} catch (\App\Exceptions\AuthorizationException $e) {
    http_response_code(403);
    try {
        echo \Core\View::make('errors.403', ['title' => 'Accès refusé', 'message' => $e->getMessage()]);
    } catch (\Throwable) {
        echo '<h1>403 — Accès refusé</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
    }

} catch (\Throwable $e) {
    \Core\Logger::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    http_response_code(500);
    // Toujours afficher l'erreur complète pour le débogage
    echo '<div style="background:#1a1a2e;color:#e94560;padding:24px;font-family:monospace;border-radius:8px;margin:20px">';
    echo '<h2 style="color:#F5A623">🚗 Kaay Dem ! — Erreur</h2>';
    echo '<strong>' . htmlspecialchars(get_class($e)) . '</strong><br><br>';
    echo htmlspecialchars($e->getMessage()) . '<br><br>';
    echo '<span style="color:#aaa;font-size:.85em">';
    echo 'Fichier : ' . htmlspecialchars($e->getFile()) . ' (ligne ' . $e->getLine() . ')<br><br>';
    echo nl2br(htmlspecialchars($e->getTraceAsString()));
    echo '</span></div>';
}
