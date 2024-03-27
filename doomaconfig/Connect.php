<?php
    class Connect
    {
        public static function getDB()
        {
            if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'],'dooma.org'))
            {
                $dbhost="doomavideo.db.5713046.hostedresource.com";
                $dbname="doomavideo";
                $dbusername="doomavideo";
                $dbpassword="Longvan78";
            }
            else
            {
                $dbhost="localhost";
                $dbname="doomavideo";
                $dbusername="root";
                $dbpassword="";
            }
            return new mysqli($dbhost, $dbusername, $dbpassword, $dbname);
        }
    }
?>