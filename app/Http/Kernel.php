protected $routeMiddleware = [
    // other middlewares...
    'auth.firebase' => \App\Http\Middleware\FirebaseAuthMiddleware::class,
];
