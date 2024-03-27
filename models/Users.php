<?php
    include_once('Model.php');
    class Users extends Model
    {
        private $columns;
        
        public function __construct($db){ 
            parent::__construct('users',$db);
            $this->columns = parent::getColumns();
        }
        public function findUser( $userName ) { return $this->getFirst( $this->selectWhere('*',"username='$userName'") ); }
        public function findUserByUserId( $userId ) { return $this->getFirst( $this->selectWhere('*', "userid='$userId'") ); }
        
        function getSalt() { return "uvgnol"; }
        
        public function addUser( $userName, $password, $birthDate, $gender ) {
            $values = array( null, $userName, $password, $birthDate, $gender, $_SERVER['REMOTE_ADDR'] );
            return $this->insert($this->columns, $values);
        }
    }
?>