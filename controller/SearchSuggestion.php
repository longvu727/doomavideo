<?php
    include_once( "Controller.php" );
    
    class SearchSuggestion extends Controller {
        public function __construct() { parent::__construct(); }
        
        public function getSuggestion($db,$inputTitle) {
            include_once($this->controllerDir . "/../models/Video.php");
            
            $video = new Video($db);
            $asciiTitles=$video->findSuggestionTitleAscii($inputTitle,10);
            $returnStr='';
            
            foreach($asciiTitles as $asciiTitle) {
                $returnStr.= "$asciiTitle[title_ascii]\n";
            }
            
            return $returnStr;
        }
        
        public function printSuggestion($db, $inputTitle) { print SearchSuggestion::getSuggestion($db, $inputTitle); }
    }
?>