<?php

$app->get('/home', function() use($app)
{
    if(!isset($_SESSION['user_id']))
    {
        $app->flash('error','Usuario no autorizado');
        $app->redirect('/');
    }
    $app->render('home.twig');
})->name('home');

