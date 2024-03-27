<?php
    class Controller
    {
        protected $controllerDir;
        public function __construct() {
            $this->controllerDir  = $_SERVER['DOCUMENT_ROOT'] ;
            $this->controllerDir .= ($_SERVER['HTTP_HOST'] == 'localhost' ) ? '/doomavideo/controller' : '/controller';
        }
    }
?>