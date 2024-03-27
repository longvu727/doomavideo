<?php
    include_once('Model.php');
    class Links extends Model
    {
        public function __construct($db){
            parent::__construct('links',$db);
        }
    }
    
    
    
    
    /*
    include_once('../doomaconfig/Connect.php');
    $db= Connect::getDB();
    $links = new Links($db);
    print_r($links->selectAll());
    */
?>