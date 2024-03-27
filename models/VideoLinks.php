<?php
    include_once('Model.php');
    class VideoLinks extends Model
    {
        public function __construct($db){ parent::__construct('video join links using(videoid)',$db);}
        public function findAllByVideoId($videoId){ return $this->selectWhere('*',"videoid='$videoId' and video.active='1'"); }
    }
?>