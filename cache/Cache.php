<?php
    class Cache {
        private $_cacheDir;
        private $_CACHETIMES = array(
            "query" => 3600,
        );
        
        public function __construct() {
            $documentRoot  = ($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT']."doomavideo" : 'C:\wamp\www\doomavideo';
            $this->_cacheDir = "$documentRoot/cache/tmp/";
        }
        
        private function generateFileName( $str ) {
            return md5($str);
        }
        
        public function cacheQuery( $query, $result ) {
            $fileName = $this->generateFileName($query);
            $FILE = file_put_contents( $this->_cacheDir."queries/$fileName", serialize($result) );
        }
        
        public function getCachedQuery( $query ) {
            $fileName = $this->generateFileName($query);
            if( $this->isCachedQuery( $query ) ) {
                return unserialize( file_get_contents($this->_cacheDir."queries/$fileName") );
            }
            return 0;
        }
        
        public function isCachedQuery( $query ) {
            $fileName = $this->_cacheDir."queries/".$this->generateFileName( $query );
            
            //file 
            if( !file_exists($fileName) || time() - filemtime($fileName) > $this->_CACHETIMES["query"] ) {
                return 0;
            }
            return 1;
        }
    }
?>