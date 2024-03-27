<?php
    include_once('Model.php');
    class CrawlerManager extends Model {
        
        private $columns ;
        
        public function __construct($db) {
            parent::__construct( 'crawler_manager', $db, array("CACHE" => 1,) ); 
            $this->columns = parent::getColumns();
        }
        
        public function getColumns() {
            return $this->columns;
        }
        
        public function findCrawlerManagerById( $crawlerManagerId ) { 
            return $this->getFirst( $this->selectWhere('*', "crawler_manager_id='$crawlerManagerId'") ); 
        }
        
        public function findCrawlerManagerByName( $name ) { 
            return $this->getFirst( $this->selectWhere('*', "name='$name'") ); 
        }
        
        public function findFamilyTree( $crawlerManagerRootId ) {
            return $this->selectWhere( '*', "root_id=$crawlerManagerRootId or crawler_manager_id=$crawlerManagerRootId order by crawler_manager_id asc" );
        }
        
        public function save( $row ) {
            $columns = array_keys($row);
            $values = array();
            foreach( $columns as $column ) { array_push($values, $row[$column]); }
            return $this->insert($columns, $values);
        }
        public function delete( $crawlerManagerId ) {
            return parent::delete("crawler_manager_id=$crawlerManagerId");
        }
        public function saveExistingRow( $columnsValues ) {
            $crawler_manager_id = $columnsValues['crawler_manager_id'];            
            $columns = array_keys($columnsValues);
            $values = array();
            foreach( $columns as $column ) { array_push($values, $columnsValues[$column]); }
            return $this->update( $columns, $values, "crawler_manager_id=$crawler_manager_id" );
        }
    }
    
?>