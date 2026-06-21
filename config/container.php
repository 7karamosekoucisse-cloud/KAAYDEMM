<?php
declare(strict_types=1);

use Core\{Container, Database};
use App\Interfaces\{
    UserRepositoryInterface,
    TripRepositoryInterface,
    BookingRepositoryInterface,
    NotifierInterface,
    AuthServiceInterface
};
use App\Repositories\{
    UserRepository, TripRepository, BookingRepository,
    ReviewRepository, MessageRepository, NotificationRepository,
    ReportRepository, WaitlistRepository
};
use App\Services\{AuthService, TripService, BookingService, ReviewService, UserService, NotificationService, StatisticsService};
use App\Controllers\{
    AuthController, SearchController, TripController, BookingController,
    DashboardController, AdminController, ProfileController,
    MessageController, NotificationController, ReviewController, ReportController
};
use App\Controllers\Api\TripController as ApiTripController;
use App\Middlewares\{AuthMiddleware, CsrfMiddleware, AdminMiddleware, DriverMiddleware};

$cfg = require __DIR__ . '/config.php';

Database::getInstance($cfg['db']);

return Container::build(array(
    // Repositories
    UserRepositoryInterface::class    => function($c) { return new UserRepository(); },
    TripRepositoryInterface::class    => function($c) { return new TripRepository(); },
    BookingRepositoryInterface::class => function($c) { return new BookingRepository(); },
    ReviewRepository::class           => function($c) { return new ReviewRepository(); },
    MessageRepository::class          => function($c) { return new MessageRepository(); },
    NotificationRepository::class     => function($c) { return new NotificationRepository(); },
    ReportRepository::class           => function($c) { return new ReportRepository(); },
    WaitlistRepository::class         => function($c) { return new WaitlistRepository(); },

    // Services
    NotifierInterface::class    => function($c) { return new NotificationService($c->get(NotificationRepository::class)); },
    NotificationService::class  => function($c) { return new NotificationService($c->get(NotificationRepository::class)); },
    AuthService::class          => function($c) { return new AuthService($c->get(UserRepositoryInterface::class)); },
    AuthServiceInterface::class => function($c) { return $c->get(AuthService::class); },

    TripService::class => function($c) {
        return new TripService(
            $c->get(TripRepositoryInterface::class),
            $c->get(BookingRepository::class),
            $c->get(NotifierInterface::class)
        );
    },
    BookingService::class => function($c) {
        return new BookingService(
            $c->get(BookingRepositoryInterface::class),
            $c->get(TripRepositoryInterface::class),
            $c->get(NotifierInterface::class),
            $c->get(UserRepositoryInterface::class)
        );
    },
    ReviewService::class => function($c) {
        return new ReviewService(
            $c->get(BookingRepositoryInterface::class),
            $c->get(ReviewRepository::class),
            $c->get(TripRepositoryInterface::class)
        );
    },
    UserService::class => function($c) {
        return new UserService(
            $c->get(UserRepositoryInterface::class),
            $c->get(NotifierInterface::class)
        );
    },
    StatisticsService::class => function($c) {
        return new StatisticsService(
            $c->get(TripRepository::class),
            $c->get(BookingRepository::class),
            $c->get(UserRepository::class),
            $c->get(ReviewRepository::class)
        );
    },

    // Middlewares
    AuthMiddleware::class   => function($c) { return new AuthMiddleware(); },
    CsrfMiddleware::class   => function($c) { return new CsrfMiddleware(); },
    AdminMiddleware::class  => function($c) { return new AdminMiddleware($c->get(UserRepositoryInterface::class)); },
    DriverMiddleware::class => function($c) { return new DriverMiddleware($c->get(UserRepositoryInterface::class)); },

    // Controllers
    AuthController::class => function($c) {
        return new AuthController(
            $c->get(AuthServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(AuthService::class)
        );
    },
    SearchController::class => function($c) {
        return new SearchController(
            $c->get(AuthServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(TripRepositoryInterface::class)
        );
    },
    TripController::class => function($c) {
        return new TripController(
            $c->get(AuthServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(TripRepositoryInterface::class),
            $c->get(TripService::class),
            $c->get(BookingService::class),
            $c->get(BookingRepositoryInterface::class)
        );
    },
    BookingController::class => function($c) {
        return new BookingController(
            $c->get(AuthServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(BookingService::class),
            $c->get(BookingRepository::class),
            $c->get(TripRepositoryInterface::class)
        );
    },
    DashboardController::class => function($c) {
        return new DashboardController(
            $c->get(AuthServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(TripRepositoryInterface::class),
            $c->get(BookingRepository::class),
            $c->get(NotificationRepository::class),
            $c->get(StatisticsService::class)
        );
    },
    AdminController::class => function($c) {
        return new AdminController(
            $c->get(AuthServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(TripRepositoryInterface::class),
            $c->get(UserService::class),
            $c->get(StatisticsService::class),
            $c->get(ReportRepository::class)
        );
    },
    ProfileController::class => function($c) {
        return new ProfileController(
            $c->get(AuthServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(UserService::class),
            $c->get(ReviewRepository::class)
        );
    },
    MessageController::class => function($c) {
        return new MessageController(
            $c->get(AuthServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(MessageRepository::class)
        );
    },
    NotificationController::class => function($c) {
        return new NotificationController(
            $c->get(AuthServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(NotificationRepository::class)
        );
    },
    ReviewController::class => function($c) {
        return new ReviewController(
            $c->get(AuthServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(ReviewService::class)
        );
    },
    ReportController::class => function($c) {
        return new ReportController(
            $c->get(AuthServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(ReportRepository::class)
        );
    },
    ApiTripController::class => function($c) {
        return new ApiTripController(
            $c->get(AuthServiceInterface::class),
            $c->get(UserRepositoryInterface::class),
            $c->get(TripRepositoryInterface::class)
        );
    },
));
