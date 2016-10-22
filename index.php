<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/vendor/autoload.php';

spl_autoload_register(function ($class_name) {
    if (file_exists($class_name . '.php')) include $class_name . '.php';
});

$app = new Silex\Application();
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'dbname'   => 'katestats',
        'user'     => 'kate',
        'password' => '123',
        'charset'  => 'utf8'
    ),
));
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

$app['twig'] = $app->share($app->extend('twig', function(Twig_Environment $twig, \Silex\Application $app) {
    $data = get_object_vars(AppData::Instance());
    foreach ($data as $key => $value) {
        $twig->addGlobal($key, $value);
    }
    return $twig;
}));

$app['debug'] = true;

$app->mount('/', new IndexControllerProvider());
$app->mount('/results', new ResultsControllerProvider());

$app->run();