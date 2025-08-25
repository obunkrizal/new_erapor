protected $middlewareAliases = [
// ... other middleware
'check.user.active' => \App\Http\Middleware\CheckUserActive::class,
'check.session.timeout' => \App\Http\Middleware\CheckSessionTimeout::class,
];

protected $middlewareGroups = [
'web' => [
// ... other middleware
'check.session.timeout',
],
'api' => [
// ... other middleware
],
];

protected $commands = [
\App\Console\Commands\GenerateSiswaImportTemplate::class,
];