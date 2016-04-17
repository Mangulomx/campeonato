<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE); 
ini_set('display_errors','On');
require '../vendor/autoload.php';
require '../config.php';
session_start();
$app = new \Slim\Slim(array(
    'view' => new \Slim\Views\Twig(),
    'templates.path' => '../templates',
    'log.enabled' => true
));
$view = $app->view();
$view->parserOptions = array(
    'debug' => true
);

//Hook para añadir todo aquello que la plantilla necesita globalmente
$app->hook('slim.before', function() use($app)
{
     $app->view()->appendData(array(
        'logged_in' => isset($_SESSION['user_id']) 
     ));
});

$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

$twig = $view->getInstance();
$authorized = false;
if(isset($_SESSION['user_id']))
{
    $user = ORM::for_table('usuario')->
    select('usuario.*')->
    where('id',$_SESSION['user_id'])->
    find_one();
    
    if($user!==false)
    {
        $authorized = ($user->admin == 1);
    }
    $twig->addGlobal('authorized',$authorized);
}
$users = ORM::for_table('usuario')->find_many();
$twig->addGlobal('users',$users);
require('../routes/session.php');
#Se va a la portada principal de mi aplicación
require('../routes/home.php');
# Gestion de usuarios de la aplicación
require('../routes/users.php');

# Gestion de participantes
require('../routes/participantes.php');

#Gestión de eventos
require('../routes/eventos.php');

$app->run();