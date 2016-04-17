<?php
    function create_config($path,$server,$db_name)
    {
        global $user, $user_pass;
        $string_config = <<<EOT
        <?php
                
         \$CFG = array(
        'host'=> '{$server}',
        'database' => '{$db_name}',
        'user' => '{$user}',
        'password' => '{$user_pass}'
       );
       ORM::configure(array(
       'connection_string' => 'mysql:host='. \$CFG['host'].';dbname='. \$CFG['database'],
       'username' => \$CFG['user'],
       'password' => \$CFG['password'],
       'driver_options' => array(PDO::MYSQL_ATTR_INIT_COMMAND=>'set NAMES utf8')));
EOT;
     
        
        try
        {
            
            $ruta = "var/www/html/olimpiada/config.php";
            $fh = fopen("../../$path","w+") or die("Error al crear el fichero de configuracion"); //Abro el fichero en mode de escritura
            fwrite($fh,$string_config); //escribo en el fichero
            fclose($fh); //cierro el fichero
            chmod($ruta,'0777');#Cambiando permisos
            $create_file = true;
            
        } catch (PDOException $e)
        { 
          die('error al crear el archivo'.$path."".$e->getMessage());
        }
        return $create_file;
    }
    
    function create_db($server,$db_name)
    {
        global $user, $user_pass, $create_db;
        try
        {
            $dbh = new PDO('mysql:host='. $server .';charset=utf8',$user,$user_pass);
            $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
            $createDB = $dbh->query('CREATE DATABASE IF NOT EXISTS '.$db_name.' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;');
            $createDB->execute();
            $create_db = true;
            
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return $create_db;
    }
    
    function create_AdminUser($server,$db_name)
    {
        global $user, $user_pass, $create_admin;
        $username = test_input($_POST['username']);
        $password = password_hash(test_input($_POST['password1']),PASSWORD_DEFAULT); //encriptacion de la contraseña
        $email = test_input($_POST['email']);
        try
        {    
            $dbh = new PDO("mysql:host=".$server.";dbname=".$db_name.";charset=utf8",$user,$user_pass);
            $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
            $createAdminUser = $dbh->prepare("INSERT INTO usuario(username,contrasenia,email,admin) VALUES(:usuario,:contrasenia,:email,true)");
            $createAdminUser->bindValue(':usuario',$username,PDO::PARAM_STR);
            $createAdminUser->bindValue(':contrasenia',$password,PDO::PARAM_STR);
            $createAdminUser->bindValue(':email',$email,PDO::PARAM_STR);
            $createAdminUser->execute(); 
            $create_admin = true;
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage()."</div>";
        }
        return $create_admin;
    }
    
    function create_tables($server,$db_name)
    {
        global $user, $user_pass,$create_tables;
        try
        {
            $dbh = new PDO('mysql:host='. $server .";dbname=". $db_name .";charset=utf8",$user,$user_pass); //me conecto a la base de datos
            $dbh->beginTransaction(); 
            /*Creacion de las tablas de la base de datos*/
            
            #tabla usuario
            
            $dbh->exec("CREATE TABLE IF NOT EXISTS `usuario` (
           `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
           `username` varchar(45) NOT NULL,
           `contrasenia` varchar(255) NOT NULL,
           `email` varchar(45) NOT NULL,
           `admin` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
            UNIQUE KEY `variable_username_email_uk`(`username`, `email`),
            PRIMARY KEY (`id`)) 
            ENGINE = InnoDB DEFAULT CHARSET=utf8; ");
               
            #tabla participante
            $dbh->exec("CREATE TABLE IF NOT EXISTS `participante` (
           `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
           `nieparticipante` varchar(10),
           `nombre` varchar(45) NOT NULL,
           `apellidos` varchar(100) NOT NULL,
           `telefono` varchar(45) NOT NULL,
           `edad` TINYINT unsigned,
           `provincia` varchar(50),
           `localidad` varchar(100),
            UNIQUE KEY `variable_nieparticipante_uk`(`nieparticipante`),
            PRIMARY KEY (`id`)) 
            ENGINE = InnoDB DEFAULT CHARSET=utf8; ");
            
            #tabla olimpiada
            $dbh->exec("CREATE TABLE IF NOT EXISTS `olimpiada`(
            `id` int(4) UNSIGNED NOT NULL AUTO_INCREMENT ,
            `tematica` TINYINT UNSIGNED NOT NULL DEFAULT 0 ,
            PRIMARY KEY (`id`)) 
            ENGINE = innoDB DEFAULT CHARSET=utf8; ");
            
            #tabla evento olimpiada
            $dbh->exect("CREATE TABLE IF NOT EXISTS evento(
            `id` int( 4 ) unsigned NOT NULL AUTO_INCREMENT ,
            `id_olimpiada` int( 4 ) unsigned NOT NULL ,
            `actividad_tematica` varchar( 100 ) NOT NULL ,
            PRIMARY KEY ( id ) ,
            FOREIGN KEY fk_evento_olimpiada( id_olimpiada ) REFERENCES olimpiada( id )
            ) ENGINE = Innodb CHARSET = utf8;");
            
            #Tabla instalaciones
            
            $dbh->exec("CREATE TABLE IF NOT EXISTS instalaciones(
            `id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT ,
            `nombreInstalacion` varchar( 100 ) NOT NULL ,
            `Localidad` varchar( 100 ) NOT NULL ,
            `Provincia` varchar(50) NOT NULL,
            UNIQUE KEY 'variable_nombreInstalacion_uk`,
            PRIMARY KEY ( id )) ENGINE = Innodb CHARSET = utf8;");
           
            $dbh->commit();
            $create_tables = true;
        }
        catch(PDOException $e)
        {
            echo "<p>Ha ocurrido un error al crear las tablas ".$ex->getMessage()."</p>";
            $dbh->rollBack(); //cancelo la transaccion
        }
        
       return $create_tables;  
    }
    
    //Quitando espacios en blanco, convirtiendo caracteres especiales a html de los campos del formulario
    function test_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    