<?php
declare(strict_types=1);

use Core\Router;
use App\Controllers\{
    AuthController, SearchController, TripController,
    BookingController, DashboardController, AdminController,
    ProfileController, MessageController, NotificationController,
    ReviewController, ReportController
};
use App\Controllers\Api\TripController as ApiTripController;
use App\Middlewares\{AuthMiddleware, CsrfMiddleware, AdminMiddleware, DriverMiddleware};

/** @var Router $router */

// ── Public ────────────────────────────────────────────────────────────────────
$router->get('/',                      [SearchController::class, 'home'],            [], 'home');
$router->get('/search',                [SearchController::class, 'search'],           [], 'search');

// ── Auth ──────────────────────────────────────────────────────────────────────
$router->get('/register',              [AuthController::class, 'showRegister'],       [], 'register');
$router->post('/register',             [AuthController::class, 'register'],           []);
$router->get('/login',                 [AuthController::class, 'showLogin'],          [], 'login');
$router->post('/login',                [AuthController::class, 'login'],              []);
$router->get('/logout',                [AuthController::class, 'logout'],             [AuthMiddleware::class]);
$router->get('/password/forgot',       [AuthController::class, 'showForgotPassword'], []);
$router->post('/password/forgot',      [AuthController::class, 'forgotPassword'],     []);
$router->get('/password/reset/{token}',[AuthController::class, 'showResetPassword'],  []);
$router->post('/password/reset/{token}',[AuthController::class,'resetPassword'],       []);

// ── Dashboard ─────────────────────────────────────────────────────────────────
$router->get('/dashboard',             [DashboardController::class, 'index'],         [AuthMiddleware::class], 'dashboard');

// ── Trips (public browse) ─────────────────────────────────────────────────────
$router->get('/trips',                 [TripController::class, 'index'],              [AuthMiddleware::class]);
$router->get('/trips/create',          [TripController::class, 'create'],             [AuthMiddleware::class, DriverMiddleware::class]);
$router->post('/trips',                [TripController::class, 'store'],              [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/trips/{id}',            [TripController::class, 'show'],               []);
$router->get('/trips/{id}/edit',       [TripController::class, 'edit'],               [AuthMiddleware::class, DriverMiddleware::class]);
$router->post('/trips/{id}/edit',      [TripController::class, 'update'],             [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/trips/{id}/cancel',    [TripController::class, 'cancel'],             [AuthMiddleware::class, CsrfMiddleware::class]);

// ── Bookings ──────────────────────────────────────────────────────────────────
$router->post('/trips/{tripId}/book',  [BookingController::class, 'book'],            [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/bookings',              [BookingController::class, 'myBookings'],       [AuthMiddleware::class]);
$router->get('/bookings/{id}',         [BookingController::class, 'show'],             [AuthMiddleware::class]);
$router->post('/bookings/{id}/confirm',[BookingController::class, 'confirm'],          [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/bookings/{id}/cancel', [BookingController::class, 'cancel'],           [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/checkin',              [BookingController::class, 'checkIn'],          [AuthMiddleware::class, CsrfMiddleware::class]);

// ── Reviews ───────────────────────────────────────────────────────────────────
$router->post('/bookings/{bookingId}/review', [ReviewController::class, 'store'],     [AuthMiddleware::class, CsrfMiddleware::class]);

// ── Profile ───────────────────────────────────────────────────────────────────
$router->get('/profile/edit',          [ProfileController::class, 'edit'],             [AuthMiddleware::class]);
$router->post('/profile/edit',         [ProfileController::class, 'update'],           [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/profile/{id}',          [ProfileController::class, 'show'],             []);
$router->get('/profile/driver/request',[ProfileController::class, 'requestDriver'],    [AuthMiddleware::class]);
$router->post('/profile/driver/request',[ProfileController::class,'submitDriverRequest'],[AuthMiddleware::class, CsrfMiddleware::class]);

// ── Messages ──────────────────────────────────────────────────────────────────
$router->get('/messages',              [MessageController::class, 'inbox'],            [AuthMiddleware::class]);
$router->get('/messages/{otherId}',    [MessageController::class, 'conversation'],     [AuthMiddleware::class]);
$router->post('/messages/{otherId}',   [MessageController::class, 'send'],             [AuthMiddleware::class, CsrfMiddleware::class]);

// ── Notifications ─────────────────────────────────────────────────────────────
$router->get('/notifications',         [NotificationController::class, 'index'],       [AuthMiddleware::class]);

// ── Reports ───────────────────────────────────────────────────────────────────
$router->post('/reports',              [ReportController::class, 'store'],             [AuthMiddleware::class, CsrfMiddleware::class]);

// ── Admin ─────────────────────────────────────────────────────────────────────
$router->get('/admin',                 [AdminController::class, 'index'],              [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/users',           [AdminController::class, 'users'],              [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/drivers',         [AdminController::class, 'pendingDrivers'],     [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/drivers/{userId}/approve', [AdminController::class,'approveDriver'],[AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/drivers/{userId}/reject',  [AdminController::class,'rejectDriver'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/users/{userId}/ban',       [AdminController::class,'banUser'],      [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/users/{userId}/unban',     [AdminController::class,'unbanUser'],    [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/trips',           [AdminController::class, 'trips'],              [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/reports',         [AdminController::class, 'reports'],            [AuthMiddleware::class, AdminMiddleware::class]);
$router->post('/admin/reports/{id}/resolve',[AdminController::class,'resolveReport'],  [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/stats',           [AdminController::class, 'stats'],              [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/export/csv',      [AdminController::class, 'exportCsv'],          [AuthMiddleware::class, AdminMiddleware::class]);

// ── API REST ──────────────────────────────────────────────────────────────────
$router->get('/api/trips',             [ApiTripController::class, 'index'],            []);
$router->get('/api/trips/popular',     [ApiTripController::class, 'popular'],          []);
$router->get('/api/trips/{id}',        [ApiTripController::class, 'show'],             []);
