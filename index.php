<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/vendor/autoload.php';
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;

$appUrl = "http://test.wp/stats";
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

require_once "data.php";
require_once "addController.php";
require_once "indexController.php";
require_once "resultsController.php";
require_once "deleteController.php";
require_once "perclassController.php";

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    global $questions, $keys, $appUrl, $results, $keysChar, $question_titles, $question_options;
    $twig->addGlobal('questions', $questions);
    $twig->addGlobal('question_titles', $question_titles);
    $twig->addGlobal('question_options', $question_options);
    $twig->addGlobal('keys', $keys);
    $twig->addGlobal('appUrl', $appUrl);
    $twig->addGlobal('keysChar', $keysChar);
    $twig->addGlobal('results', $results);
    return $twig;
}));
$app['debug'] = true;

$app->post('/add', $addController);

$app->get('/', $indexController);

$app->get('/results', $resultsController);

$app->get('/results/{klass}', $perclassController);

$app->get('/delete/{id}', $deleteController);

$app->run();