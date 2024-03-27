<?php
    include_once( "Controller.php" );
    
    class Episode extends Controller {
        public function __construct() { parent::__construct(); }
        
        public function getLinksByVideoId($db, $videoId) {
            include_once($this->controllerDir . "/../models/VideoLinks.php");
            
            $videoLinks = new VideoLinks($db);
            $videoArray= $videoLinks->findAllByVideoId($videoId);
            
            for($i=0;$i<count($videoArray);$i++) {
                $video=$videoArray[$i];
                if(strstr($video['embeddedlink'], 'megavideo')) {
                    list($domain,$parameter)=explode('?',$video['embeddedlink']);
                    $parameter=implode('/',explode('=',$parameter));
                    $domain=trim($domain,'/');
                    $videoArray[$i]['embeddedlink']="$domain/$parameter";
                }
            }
            
            return $videoArray;
        }
    }
?>