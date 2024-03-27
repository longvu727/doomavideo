<?php
    include_once('Model.php');
    class ContentScrapes extends Model {
        
        public function __construct($db) {
            parent::__construct('content_scrapes',$db);            
        }
        
        public function find( $columnsValues ) {
            $crawler_manager_id = $columnsValues['crawler_manager_id'];            
            $columns = array_keys($columnsValues);
            $where = array();
            foreach( $columns as $column ) { 
                $where[] = "$column='$columnsValues[$column]'"; 
            }
            return $this->selectWhere( '*', join(" and ", $where) );
        }
        
        public function save( $row ) {
            $columns = array_keys($row);
            $values = array();
            foreach( $columns as $column ) { array_push($values, $row[$column]); }
            return $this->insert($columns, $values);
        }
        
        public function deleteByCrawlerManagerId( $crawlerManagerId ) {
            return parent::delete("crawler_manager_id=$crawlerManagerId");
        }
        
        public function delete( $contentScrapeId ) {
            return parent::delete("content_scrape_id=$contentScrapeId");
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