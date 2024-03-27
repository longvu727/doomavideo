<?php
    if( isset($DOOMA_GLOBALS) ) {
        include_once("$DOOMA_GLOBALS[siteRoot]./cache/Cache.php");
    }
    else {
        print "GLOBAL VARIABLES NOT SET\n";
    }
    class Model
    {
        protected $tableName;
        protected $db;
        private $logFiles;
        #CACHE
        private $options = array(
            "CACHE" => 0,
        );
        
        public function __construct($tableName, $db, $options=null) {
            $this->tableName=$tableName;
            $this->db=$db;
            foreach( array_keys($this->options) as $optionName ) {
                if( isset($options[$optionName]) ) {
                    $this->options[$optionName] = $options[$optionName];
                }
            }
            $documentRoot  = $_SERVER['DOCUMENT_ROOT'] ;
            
            $modelDir = $documentRoot;
            $modelDir .= ( isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] == 'localhost' ) ? '/doomavideo/models' : '/models';
            
            $this->logFiles=
                array
                (
                    "MYSQLERRORLOG" => "$modelDir/../log/mysqlerror.log",
                    "ERRORLOG" => "$modelDir/../log/error.log",
                );
        }
        
        public function setCache( $cache=0 ) {
            $this->_CACHE = $cache;
        }
        
        private function log($str, $logType) {
            if(!isset($this->logFiles[$logType])) {
                $str="**$logType log file not found**\n".$str;
                $logType='ERRORLOG';
            }
            $str="************************************************\n$str\n************************************************\n";
            file_put_contents($this->logFiles[$logType], "$str\n", FILE_APPEND | LOCK_EX);
        }
        
        public function getColumns() {
            $query="SELECT group_concat(column_name) as column_name FROM information_schema.columns WHERE table_name = '$this->tableName'";
            $result=$this->query($query,1);

            if( empty($result) ) { return 0; }
            else { 
                return explode( ',', $result[0]['column_name'] ); 
           }
        }
        
        public function selectAll() {
            $query="SELECT * FROM $this->tableName";
            $result=$this->query($query,1);
            
            if( empty($result) ) { return 0; }
            else { return $result; }
        }
        /**
         * Return an array of rows from select statement
         *      select $columns from $this->tableName where $whereClause
         * @param object $columns [optional], defaulted as "*"
         * @param object $whereClause, mysql where clause
         * @return an array of rows
         */
        public function selectWhere($columns='*', $whereClause) {
            $query="SELECT $columns FROM $this->tableName WHERE $whereClause";
            return $this->query($query,1);
        }
        
        public function query($query, $processResult=0) {
            $query = preg_replace("/'now\(\)'/", 'now()', $query); //replace 'now()' to now()
            //if cached return cached results
            if( $this->options['CACHE'] ) {
                $cache = new Cache();
                if( $cache->isCachedQuery($query) ) {
                    return $cache->getCachedQuery( $query );
                }
            }
            
            $result = $this->db->query($query);
            if (!$result) {$this->log($query."\n".$this->db->error,'MYSQLERRORLOG');return 0;}
            
            if($processResult) {
                $processedResult = $this->_processResults($result);
                if( $this->options['CACHE'] ) {
                    $cache = new Cache();
                    $cache->cacheQuery( $query, $processedResult );
                }
                return  $processedResult;
            }
            else return 1;
        }
        
        public function getFirst( $resultArray ){
            if( !empty($resultArray) ) {
                return array_shift($resultArray);
            }
            return 0;
        }
        
        function _processResults($mysqliResult) {
            $resultArray=array();
            while ($row = $mysqliResult->fetch_assoc()) {array_push($resultArray,$row);}
            return $resultArray;
        }
        
        function insert($columns, $values) {
            if(count($columns)!=count($values)){return 0;}
            
            $valueStr='';
            $columnStr='';
            
            for($i=0;$i<count($columns);$i++) { 
                $value=$this->db->escape_string($values[$i]);
                $valueStr.="'$value',"; 
            }
            $valueStr=trim($valueStr,',');
            $columnStr=implode(',',$columns);
            
            $query="insert into $this->tableName ($columnStr) values ($valueStr)";
            
            $this->query($query,0);
            return mysqli_insert_id($this->db);
        }
        
        function update($columns, $values, $where=1) {
            if(count($columns)!=count($values)){return 0;}
            
            $query="update $this->tableName set ";
            for($i=0;$i<count($columns);$i++) 
            { 
                $value=$this->db->escape_string($values[$i]);
                $query.="$columns[$i]='$value', "; 
            }
            //remove the last comma
            $query=trim($query,", ");
            
            $query.=" where $where";     
            return $this->query($query,0);
        }
        
        function delete( $whereClause ) {
            $query ="delete from $this->tableName where $whereClause";
            return $this->query( $query, 0 );
        }
    }
    
    
    
    
    /*
    include_once('../doomaconfig/Connect.php');
    $db= Connect::getDB();
    $model = new Model('video', $db);
    print_r($model->selectAll());
    */
?>