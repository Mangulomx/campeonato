<?php
#Listado de usuarios

$app->get('/users', function() use ($app, $authorized,$users)
{ 
    $app->render('users.twig',array('users' => $users, 'is_admin' => $authorized));
})->name('userList');

# Alta de usuarios

$app->map('/altausers', function() use($app)
{
    $error = array();
    if(isset($_POST['create-user']))
    {
        $username = $app->request()->post('inputuser');
        $password = $app->request()->post('inputpassword');
        $email = $app->request()->post('inputemail');
        $is_admin = (int)$app->request()->post('es_admin',0);
    
        #valido si hay errores
        if(empty($username))
        {
            $error[] = "El nombre del usuario no puede estar vacio";
        }
        else
        {
            //Compruebo si existe el usuario
            $user = ORM::for_table('usuario')->
            where('username',$username)->
            find_one();
            
            if($user)
            {
                $error[] = "El nombre del usuario ya esta en uso";
            }
        }
        if(strlen($password)<6)
        {
            $error[] = "La contraseña debe tener un mínimo de 7 caracteres";
        }
        #Validación del email
        if(!empty($email))
        {
            if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $error[] = "El email no es valido";
            }
        }
    
        #Si no hay errores procedemos a crear el usuario
    
        if(count($error)==0)
        {
            $user = ORM::for_table('usuario')->create();
            $user->username = filter_var($username, FILTER_SANITIZE_STRING);
            $user->contrasenia = password_hash($password, PASSWORD_DEFAULT);
            $user->email = filter_var($email, FILTER_SANITIZE_EMAIL);
            $user->admin = $is_admin;
            $user->save();
            
            $app->flash('success','Usuario creado correctamente');
            $app->redirect($app->urlFor('userList'));
        }
        else 
        {
            $app->flash('error', $error);    
            $app->redirect($app->urlFor('altausers'));
        }
        
    }
    $app->render('altausers.twig');
})->VIA('GET','POST')->name('altausers');

#Borrar usuarios

$app->post('/deleteUser', function() use($app)
{
    if(isset($_POST['eliminar']))
    {
        $usuarios = $app->request()->post('rusuario');
        
        foreach($usuarios as $valor)
        {
            if($_SESSION['user_id']!== $valor)
            {
                $query = ORM::for_table('usuario')->find_one($valor);
                if($query)
                {
                    $query->delete();
                }
            }
            else
            {
                $app->flash('error',"No puedes borrar el id ".$_SESSION['user_id']." con que te has logueado");
            }
        }
        $app->redirect($app->urlFor('userList'));
    }
})->name("userDelete");





