<?php
    include_once( "Controller.php" );
    
    class Login extends Controller {
        private $user;
        private $db;
        private static $MAXSESSIONTIME ;
        
        public function __construct( $db ) { 
            parent::__construct();
            
            $this->MAXSESSIONTIME= 86400;//one day
            $this->db = $db; 
        }
        
        public function findUser( $userName ) {
            include_once($this->controllerDir . "/../models/Users.php");
            
            $users = new Users($this->db);
            $this->user = $users->findUser( $userName );
            
            return $this->user;
        }
        
        public function validateUser( $userName, $password ) {
            $userRow = $this->findUser( $userName );
            
            include_once($this->controllerDir . "/../models/Users.php");
            $users = new Users($this->db);
            
            $salt = $users->getSalt();
            if( md5($userRow["password"].$salt) == $password ) {//$userRow["password"] == $password ) {// 
                return 1; 
            }
            return 0;
        }
        
        /**
         * returns 0 if not valid, otherwise return parameter $sessionRow
         * @param object $sessionRow
         * @return 
         */
        public function validateSession( $sessionId ) {
            
            include_once($this->controllerDir . "/../models/Sessions.php");
            $sessions = new Sessions( $this->db );
            $sessionRow = $sessions->findSessionBySessionId($sessionId);
            
            $sessionTime = time() - strtotime( $sessionRow['timestamp'] );
            if( $sessionTime < $this->MAXSESSIONTIME) {
                return $sessionRow;
            }
            return 0;
        }
        
        public function createSession( $userName="", $ip="" ) {
            include_once($this->controllerDir . "/../models/Sessions.php");
            $session = new Sessions( $this->db );
            
            $sessionRow;
            
            if( !isset($this->user) ){
                $this->user = $this->findUser($userName);
            }
            
            if(  $sessionRow = $session->findSessionByUserIdAndIp( $this->user['userid'], $ip )  ) {
                $session->updateSessionTime($sessionRow['sessionid']);
            }
            else {
                $sessionId = md5("$userName $ip");
                
                $session->insertSession( $sessionId, $this->user['userid'], $ip );
                $sessionRow = $session->findSessionBySessionId( $sessionId );
            }
            
            setcookie( "DOOMA_SESSION", $sessionRow['sessionid'] );
        }
        
        public function removeSession( $sessionId ) {
            include_once($this->controllerDir . "/../models/Sessions.php");
            $session = new Sessions( $this->db );
            
            $session->deleteSession( $sessionId );
            setcookie( "DOOMA_SESSION", $sessionId, time()-3600 );
        }
    }
?>