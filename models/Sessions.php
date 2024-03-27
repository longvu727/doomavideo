<?php
    include_once('Model.php');
    class Sessions extends Model {
        
        public $columns;
        private $maxSessionTime = 86400;
        
        public function __construct($db){ 
            parent::__construct('sessions',$db);
            $this->columns = parent::getColumns();
       }
        
        public function updateSessionTime( $sessionId ) { return $this->update("timestamp", "now()", "sessionid='$sessionId'"); }
        
        public function findSessionBySessionId( $sessionId )        { return $this->getFirst( $this->selectWhere('*',"sessionId='$sessionId'") ); }
        public function findSessionByUserId( $userId )              { return $this->getFirst( $this->selectWhere('*',"userid='$userId'") ); }
        public function findSessionByUserIdAndIp( $userId, $ip )    { return $this->getFirst( $this->selectWhere('*',"userid='$userId' and ip='$ip'") ); }
        
        public function insertSession( $sessionId, $userid, $ip )   { $this->insert( $this->columns, array( $sessionId, $userid, $ip ) ); }
        public function deleteSession( $sessionId )                 { return $this->delete( "sessionid = '$sessionId'" ) ; }
    }
?>