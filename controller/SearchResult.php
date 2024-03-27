<?php
    include_once( "Controller.php" );
    class SearchResult extends Controller{
        public function __construct() { parent::__construct(); }
        
        public function searchMovieByTitle($db, $inputTitle, $videoPerPage, $pageNumber){
            include_once($this->controllerDir . "/../models/Video.php");
            
            $video = new Video($db);
            return $video->findBySimilarTitleAscii($inputTitle, $videoPerPage, $pageNumber);
        }
    }
?>