<?php
    include_once( "Controller.php" );
    class Register extends Controller {
        private $captchaKey="6LfpHsoSAAAAAGtR6ymrp_plu49kcMrSZ4qkN1YX";
        
        public function __construct() { parent::__construct(); }
        
        public function getCaptcha() {
            include_once($this->controllerDir . "/../lib/php/recaptcha-php-1.11/recaptchalib.php");
            return recaptcha_get_html($this->captchaKey);
        }
        
        public function checkCaptcha() {
            include_once($this->controllerDir . "/../lib/php/recaptcha-php-1.11/recaptchalib.php");
            
            return 
                recaptcha_check_answer (
                    $captchaKey,
                    $_SERVER["REMOTE_ADDR"],
                    $_POST["recaptcha_challenge_field"],
                    $_POST["recaptcha_response_field"]
                );

        }
        
        public function createAccount( $db, $userName, $password, $birthDate, $gender ) {
            include_once($this->controllerDir . "/../models/Users.php");
            
            $user = new Users( $db );
            $salt = $user->getSalt();
            $password = md5($password.$salt);
            return $user->addUser( $userName, $password, $birthDate, $gender );
        }
        
        public function userNameExists( $db, $userName ) {
            include_once($this->controllerDir . "/../models/Users.php");
            
            $user = new Users( $db );
            if( $user->findUser( $userName ) ) {
                return 1;
            }
            
            return 0;
        }
    }
?>