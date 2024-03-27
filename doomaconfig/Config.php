<?php
    class Config
    {
        public static function getGlobals() {
            $DOOMA_GLOBALS = 
                array(
                    "siteRoot"  => ( isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'localhost' ) ? $_SERVER['DOCUMENT_ROOT'] . "/doomavideo" : $_SERVER['DOCUMENT_ROOT'],
                    'siteUrl'   => ( isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'localhost' ) ? 'http://localhost/doomavideo' : 'http://www.dooma.org',
                );
            $DOOMA_GLOBALS['viewRoot']        = $DOOMA_GLOBALS['siteRoot'] . "/views";
            $DOOMA_GLOBALS['controllerRoot']  = $DOOMA_GLOBALS['siteRoot'] . "/controller";
            
            return $DOOMA_GLOBALS;
        }
        public static function getDB() {
            if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'],'dooma.org') !== false) {
                $dbhost="doomavideo.db.5713046.hostedresource.com";
                $dbname="doomavideo";
                $dbusername="doomavideo";
                $dbpassword="Longvan78";
            }
            else {
                $dbhost="localhost";
                $dbname="doomavideo";
                $dbusername="root";
                $dbpassword="";
            }
            return new mysqli($dbhost, $dbusername, $dbpassword, $dbname);
        }
    }
?>