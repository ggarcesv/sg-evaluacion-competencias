<?php
/**
 * Created by PhpStorm.
 * User: arkurogane
 * Date: 31-12-2017
 * Time: 15:25
 */

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';

session_start();

$baseUrl='';
$baseDir= str_replace(basename($_SERVER['SCRIPT_NAME']), '',$_SERVER['SCRIPT_NAME']);

$baseUrl='http://'.$_SERVER['HTTP_HOST'].$baseDir;

define('BASE_URL',$baseUrl);

$dotenv= new \Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => getenv('DB_HOST'),
    'database'  => getenv('DB_NAME'),
    'username'  => getenv('DB_USER'),
    'password'  => getenv('DB_PASS'),
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

$route = $_GET['route']?? '/';


use Phroute\Phroute\RouteCollector;

$router = new RouteCollector();

$router->filter('auth', function (){
    if (!isset($_SESSION['userId']))
    {
        header('Location:' . BASE_URL . 'auth/login');
        return false;
    }
});

$router->controller('/auth', App\Controllers\AuthController::class);
$router->group(['before'=>'auth'], function ($router){
$router->controller('/admin', App\Controllers\Admin\IndexController::class);
$router->controller('admin/docente',App\Controllers\Admin\DocenteController::class);
$router->controller('admin/academico',App\Controllers\Admin\AcademicoController::class);
$router->controller('admin/criterios',App\Controllers\Admin\CriteriosController::class);
$router->controller('admin/evaluacion',App\Controllers\Admin\EvaluacionController::class);

});
$router->controller('/', App\Controllers\IndexController::class);

$dispatcher = new Phroute\Phroute\Dispatcher($router->getData());

$response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], $route);

echo $response;
