<?php

#Muestro listado de participantes
$app->get("/participante", function() use($app, $authorized)
{
    $participantesL = listadoParticipantes();
    $app->render("participante.twig", array('participantes' => $participantesL, 'is_admin' => $authorized));
})->name('participantesList');

#Alta de participante
$app->map("/AltaPart", function() use($app, $authorized, $users){
   $error = array();
   if(isset($_POST["create-participante"]))
   {
       
       $nieP = $app->request->post('nieP');
       $nombreP = $app->request->post('nombreP');
       $apellidoP = $app->request->post('apellidoP');
       $telefonoP = $app->request->post('telefonoP');
       $fechaNP = $app->request->post('fechaNP');
       $provinciaP = $app->request->post('provinciaP');
       $localidadP = $app->request->post('localidadP');
      
       $error = validar_datos($nombreP, $telefonoP, $nieP, $error);
   
       //Si no hay errores lo creamos
       
       if(count($error)==0){
           $participante = ORM::for_table('participante')->create();
           $participante->nieparticipante = $nieP;
           $participante->nombre = $nombreP;
           $participante->apellidos = $apellidoP;
           $participante->telefono = $telefonoP;
           $participante->f_nacimiento = $fechaNP;
           $participante->provincia = $provinciaP;
           $participante->localidad = $localidadP;
           $participante->save();
           $app->flash("success","Usuario con ".$nieP." creado correctamente ");
           $app->redirect($app->urlFor('participantesList'));
       }
       else
       {
           $app->flash("error",$error);
           $app->redirect($app->urlFor('AltaParticipante'));
       }  
   }
   $app->render("altaParticipante.twig", array('usuarios' => $users, 'is_admin' => $authorized));
})->name('AltaParticipante')->via('GET','POST');

#Borrar el participante
$app->post('/DeleteParti', function() use($app)
{
    if(isset($_POST['eliminar']))
    {
        $participantes = $app->request->post('chkparticipante');
        foreach($participantes as $valor)
        {
            $query = ORM::for_table('participante')->find_one($valor);
            if($query)
            {
                try
                {
                    $query->delete();
                } catch (Exception $ex) {
                    die("Ha ocurrido un error".$ex);
                }
            }
        }
        $app->redirect($app->urlFor('participantesList'));
    }
})->name('participanteDelete');

#Mostrar participantes segun identificador

$app->get('/participante/:id', function($id) use($app){
    $participante = getParticipante($id);
    $app->render('editParticipante.twig', array('participante' => $participante));
})->name('editParticipante');

#Actualizar empleado según identificador
$app->post('/participante/:id', function($id) use($app)
{
    $error = array();
    $expr_tlfno = "/^[9|6|7][0-9]{8}$/";
    if(isset($_POST['edit-participante']))
    {
        $nie = $app->request()->post('inputnie');
        $nombre = $app->request()->post('inputnameP');
        $apellidos = $app->request()->post('inputapellidoP');
        $telefono = $app->request()->post('inputtelefonoP');
        $fechaN = $app->request()->post('inputfechaNP');
        $provincia = $app->request()->post('inputprovinciaP');
        $localidad = $app->request()->post('localidadP');
        #Valido si hay errores
        if(!empty($nie))
        {
            $participante = ORM::for_table('participante')->
            where_not_equal('id',$id)->
            where('nieparticipante',$nie)->find_one();
            if($participante)
            {
                $error[] = "Ya existe un participante con ese nif";
            }
        }
        if(empty($nombre))
        {
            $error[] = "El nombre del participante no puede estar vacio";
        }
        if(!empty($telefono))
        {
            if(!preg_match($expr_tlfno,$telefono))
            {
                $error[] = "El teléfono no es correcto";
            }
        }
        
        #Si no hay errores procedo a crear el participante
        
        if(count($error)==0)
        {
            $participante = ORM::for_table('participante')->find_one($id);
            $participante->nieparticipante = $nie;
            $participante->nombre = $nombre;
            $participante->apellidos = $apellidos;
            $participante->telefono = $telefono;
            $participante->f_nacimiento = $fechaN;
            $participante->provincia = $provincia;
            $participante->localidad = $localidad;
            $participante->save();
            $app->redirect($app->urlFor('participantesList'));
        }
        else
        {
            $app->flash('error',$error);
            $app->redirect($app->urlFor('updateParticipante',array('id' => $id)));
        }

         
    }
})->name('updateParticipante');

#Validación de los datos pasando los campos a validar
function validar_datos($nombreP, $telefonoP, $nieP, $error)
{
    
    if(empty($nombreP))
    {
        $error[] = "El nombre del participante es obligatorio";
    }
    if(!empty($telefonoP))
    {
        if(!preg_match('/^[0-9]{9}$/',$telefonoP))
        {
               $error[]="El teléfono no es valido";
        }
    }
    if(!empty($nieP))
    {
        if($nieP<=0)
        {
               $error[] = "El NIE introducido no es valido.";
        }
        else
        {
               $participanteNie = ORM::for_table('participante')->
               where('nieparticipante',$nieP)->
               find_one();
               if($participanteNie)
               {
                   $error[] = 'El NIE ya está en uso.';
               }   
        }
    }
    return $error;
}

#Obtengo el participante según su identificador

function getParticipante($id_participante)
{
    return ORM::for_table('participante')->
    select('participante.*')->
    where('id',$id_participante)->
    find_one();
}

#Obtengo el listado de participantes
function listadoParticipantes()
{
    return ORM::for_table('participante')->order_by_asc("nombre")->order_by_desc("apellidos")->find_many();
}

