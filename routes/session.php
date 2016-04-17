<?php

$app->get('/', function() use($app){
   $app->redirect('/login'); 
});
$app->map('/login', function() use($app)
{
    if(isset($_POST['entrar']))
    {
        $username = $app->request->post('txt_uname');
        $password = $app->request->post('txt_password');
        $user = ORM::for_table('usuario')->where('username',$username)->find_one();
        $isValid = false;
        if($user)
        {
           #Compruebo que las contraseñas coincidan
           if(password_verify($password, $user->contrasenia))
           {
               $isValid = true;
           }
        }
        if(!$isValid)
        {
            $app->flash('error','Usuario y/o contraseña incorrecta');
            $app->redirect($app->urlFor('login'));
        }
        else
        {
            $_SESSION['user_id'] = $user->id;
            $app->redirect($app->urlFor('home'));
        }
    }
    $app->render('login.twig', array(
        'title' => 'login',
        'url' => $app->urlFor('login')
    ));
})->name('login')->via('GET','POST');

$app->get('/logout', function() use($app)
{
    //Deshacemos la variable de sesion
    unset($_SESSION['user_id']);
    //y redirijo al login
    $app->redirect('/login');
})->name('logout');
