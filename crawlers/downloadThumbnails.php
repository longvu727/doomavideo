<?php
    include_once('../doomaconfig/Connect.php');
    include_once('../models/Video.php');
    
    $db=Connect::getDB();
    $thumbNailDir='C:\wamp\www\doomavideo\images\thumbnails';
    
    $video = new Video($db);
    foreach($video->selectAll() as $row) 
    {
        if(empty($row['thumbnail'])){ continue;}
        
        $imageType=end(explode('.',$row['thumbnail']));
        print "processing $row[videoid] $row[title_ascii]\n";
        
        $video->update(array('thumbnail'),array("/images/thumbnails/$row[videoid].$imageType"),"videoid=$row[videoid]");
        
        if(!file_exists("$thumbNailDir/$row[videoid].$imageType"))
            { file_put_contents("$thumbNailDir/$row[videoid].$imageType", file_get_contents($row['thumbnail']));}
    }
?>