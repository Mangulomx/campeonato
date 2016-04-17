        <?php
                
         $CFG = array(
        'host'=> 'localhost',
        'database' => 'olimpiada',
        'user' => 'root',
        'password' => 'carriles'
       );
       ORM::configure(array(
       'connection_string' => 'mysql:host='. $CFG['host'].';dbname='. $CFG['database'],
       'username' => $CFG['user'],
       'password' => $CFG['password'],
       'driver_options' => array(PDO::MYSQL_ATTR_INIT_COMMAND=>'set NAMES utf8')));