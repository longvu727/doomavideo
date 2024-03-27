<?php
    include_once('Model.php');
    class UsersSessions extends Model {
        
        public function __construct($db){ parent::__construct('users join sessions using(userid)',$db);}
        
        public function getUserSessionByUserId( $userId )       { return $this->getFirst( $this->selectWhere("*", "userid='$userId'") ); }
        public function getUserSessionBySessionId( $sessionId ) { return $this->getFirst( $this->selectWhere("*", "sessionId='$sessionId'") ); }
    }
?>