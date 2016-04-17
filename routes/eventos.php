<?php

#Muestro el listado de eventos deportivos

$app->get("/eventos", function() use($app,$authorized){
    $eventosL = listadoEventos();
    $app->render("evento.twig", array('eventos' => $eventosL, 'is_admin' => $authorized));
})->name('eventosList');

#Alta de eventos

$app->map('/altaevent', function() use($app, $authorized){
    $error = array();
    if(isset($_POST["create-evento"]))
    {
        $event = (int)$app->request->post("selectEvento");
        $tematica = $app->request->post("inputtematica");
        //convierto cadena a minúsculas
        $temaMinus = strtolower($tematica);

    
        //Validación de errores
        if($event == -1)
        {
            $error[] = "Debes seleccionar un evento";
        }
        if(!empty($tematica))
        {
            $evento = ORM::for_table('evento')->
            where('id_olimpiada',$event)->
            where('actividad_tematica', $temaMinus)->find_one();
            if($evento)
            {
                $error[] = "Ya hay un evento ".$tematica. " para la jornada ".$event;  
            }
        }
        if(count($error)==0)
        {
            $AltaEvento = ORM::for_table('evento')->create();
            $AltaEvento->id_olimpiada = $event +1;
            $AltaEvento->actividad_tematica = $temaMinus;
            $AltaEvento->save();
            $app->redirect($app->urlFor('eventosList'));
        }
        else
        {
            $app->flash('error',$error);
            $app->redirect($app->urlFor('AltaEvento'));
        }
    }
    $app->render('altaevento.twig');
})->VIA('GET','POST')->name('AltaEvento');

#Editar eventos
$app->get('/EditEvento/:id', function($id) use($app, $authorized){
    $evento = getEvento($id);
    $app->render('editEvento.twig', array('evento' => $evento, 'is_admin' => $authorized));
   
})->name('EditEvento');

#Actualizar evento según identificador
$app->post('/EditEvento/:id', function($id) use($app, $authorized){
    $error = array();
    if(isset($_POST['edit-evento']))
    {
        $tematica = (int)$app->request()->post('selectEvento');
        $actividad_tematica = $app->request()->post('inputtematica');
        
        #Valido si hay errores
        
        if($tematica == -1)
        {
            $error[] = "Debes seleccionar una temática";
        }
        if(!empty($actividad_tematica))
        {
            $existe_tematica = ORM::for_table('evento')->
            where_not_equal('id', $id)->
            where('actividad_tematica', $actividad_tematica)->
            find_one();
            if($existe_tematica)
            {
                $error[] = "Ya existe la actividad ".$actividad_tematica;
            }
        }
        if(count($error)==0)
        {
            $evento = ORM::for_table('evento')->find_one($id);
            $evento->id_olimpiada = $tematica +1;
            $evento->actividad_tematica = $actividad_tematica;
            $evento->save();
            $app->redirect($app->urlFor('eventosList'));
        }
    }
});

#Borrar evento
$app->post('/deleteEvento', function() use($app){
    $eventos = $app->request()->post('revento');
    foreach($eventos as $valor)
    {
        $query = ORM::for_table('evento')->find_one($valor);
        if($query)
        {
            $query->delete();
        }
        else 
        {
            $app->flash('error','No se puede borrar el identificador '.$valor);
        }
    }
    $app->redirect($app->urlFor('eventosList'));
    
})->name('EventoDelete');
function listadoEventos()
{
    return ORM::for_table('evento')->order_by_asc("actividad_tematica")->find_many();
}

function getEvento($identificador)
{
    return ORM::for_table('evento')->
    table_alias('e')->
    select('e.*')->
    where('e.id',$identificador)->
    find_one();
}
