<?php
    include_once( "Controller.php" );
    
    class Search extends Controller {
        public function __construct() { parent::__construct(); }
        
        public function searchMovieTitle($db, $inputTitle) {
            include_once($this->controllerDir . "/../models/Video.php");
            
            $video = new Video($db);
            $videoArray=$video->findByTitleAscii($inputTitle);
            
        }
    }
?>