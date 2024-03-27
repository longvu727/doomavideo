<?php
    set_time_limit(0);
    include('../lib/php/simple_html_dom.php');
    include_once('../doomaconfig/Connect.php');
    include_once('../models/Video.php');
    
    $db=Connect::getDB();
    $video = new Video($db);
    
    $siteUrl='http://iphim.tv/';
    $thumbNailDir='C:\wamp\www\doomavideo\images\thumbnails';
    $htmlLinks=array('http://iphim.tv/');//
    
    $videoIdsTitlesInserted=array();
    
    $html = file_get_html($htmlLink);
        
    $moviePages = $html->find('div[class=box] div');
    $thumbnailLink;
    $count=0;
    
    foreach( $moviePages as $moviePage) 
    {
        if($moviePage->getAttribute('class')=='boxcaption'){$count++;}
        if($count>1){break;}
        
        if($posterImageLink=$moviePage->find('.thumby')) { $thumbnailLink='http://iphim.tv/'.$posterImageLink[0]->src; }
        
        //get title
        $anchorTitle=$moviePage->find('a');
        $TITLE=$anchorTitle[0]->getAttribute('title');
        $TITLE= preg_replace('/\(.*/','',$TITLE);

        print "Processing $TITLE...<br/>\n";
        
        //get total episode
        preg_match('/\d+\/(\d+)|\d+eps/i', $anchorTitle[0]->getAttribute('title'), $TOTALEPISODE);
        
        if(!$TOTALEPISODE){$TOTALEPISODE=0;}
        if(stristr($TOTALEPISODE[0], 'eps')){$TOTALEPISODE=preg_replace('/eps/i','',$TOTALEPISODE[0]);}
        else if(isset($TOTALEPISODE[1])){$TOTALEPISODE=$TOTALEPISODE[1];}
        else {$TOTALEPISODE=0;}
        
        //get show movie link
        $episodePageLink=$anchorTitle[0]->href;
        //get category
        $anchorCategory = $movieItems[2]->find('a');
        $CATEGORY = $anchorCategory[0]->innertext();
        
        $DESCRIPTION='';
        $IMAGE='';
        $SUBTITLE='';
        $THUMBNAIL='';
        $LARGE_THUMBNAIL='';
        $LANGUAGE='vietnamese';
        
        $episodePage = file_get_html($episodePageLink);
        
        //thumnail link
        if(!strstr($thumbnailLink,'no_poster.gif')) { $THUMBNAIL=$thumbnailLink; }
        
        //get post image, large image in episode page
        if($imageLinks=$episodePage->find('.box img')) 
        { 
            $largePosterImageLink;
            foreach ($imageLinks as $imageLink) { if($imageLink->alt=='postimage'){$largePosterImageLink=$imageLink;break;} }
            
            $LARGE_THUMBNAIL=$largePosterImageLink->src;
        }
        
        $query="select videoid, count(1) as count from video where title='$TITLE'";
            
        $result=$db->query($query);
        $videoExist=$result->fetch_assoc();
        
        $VIDEOID= $videoExist['videoid'];
        if(!$videoExist['count'])
        {
            $VIDEOID= $video->insert( array('videoid', 'title', 'description', 
                                'image', 'totalepisode', 'subtitle', 
                                'language', 'category', 'thumbnail', 'large_thumbnail'),
                            array('NULL',$TITLE, $DESCRIPTION,
                                $IMAGE, $TOTALEPISODE, $SUBTITLE,
                                $LANGUAGE, $CATEGORY, $THUMBNAIL, $LARGE_THUMBNAIL)
                            );
            $videoIdsTitlesInserted[$VIDEOID]= $TITLES;
        }
        
        //download images
        if(!empty($THUMBNAIL))
        {
            $imageType=end(explode('.',$THUMBNAIL));
        
            $video->update(array('thumbnail'),array("/images/thumbnails/$VIDEOID.$imageType"),"videoid=$VIDEOID");
            
            if(!file_exists("$thumbNailDir/$VIDEOID.$imageType"))
                { print $THUMBNAIL; file_put_contents("$thumbNailDir/$VIDEOID.$imageType", file_get_contents($THUMBNAIL));} 
        }
        
        //process links
        $episodeLinks = $episodePage->find('div[id=playlist] ul li a');
        foreach ($episodeLinks as $episodeLink)
        {
            $TOTALPART='';
            $EPISODENUM='';
            $PART='';
            
            //get episode num and each part
            $episodePart=$episodeLink->innertext();
            preg_match_all("/\d+(\D\d+)?/", $episodePart, $matches);
            
            $episodePart=isset($matches[0][0])?$matches[0][0]:1;
            
            if(preg_match('/(\d+)\D(\d+)/', $episodePart, $matches))//if(strstr($episodePart, '-'))
            {
                //explode('-',$episodePart);
                $EPISODENUM=$matches[1];
                $PART=$matches[2];
            }
            else if ($CATEGORY=='Phim Bộ') {$EPISODENUM=$episodePart;}
            else if ($CATEGORY=='Phim Lẻ') {$EPISODENUM=1;$PART=$episodePart;}
            
            //extract only number and dash -

            $EMBEDDEDLINK= preg_replace('/^Play\(\'/','',$episodeLink->getAttribute('onclick'));
            $EMBEDDEDLINK= urldecode( preg_replace('/\'.*$/','',$EMBEDDEDLINK) );
            
            $query="select count(1) as count from links where videoid='$VIDEOID' and embeddedlink='$EMBEDDEDLINK'";
            
            $result=$db->query($query);
            $linkExist=$result->fetch_assoc();
            
            if(!$linkExist['count'])
            {
                $query="insert into 
                            links(linkid, videoid, episode, part, totalpart, embeddedlink)
                            values(NULL,'$VIDEOID','$EPISODENUM','$PART','$TOTALPART','$EMBEDDEDLINK')";
                $db->query($query);
            }
            //else{print "$EMBEDDEDLINK existed\n";}
        }
        
        $episodePage->clear(); 
        unset($episodePage);
    }
    $html->clear(); 
    unset($html);
        
    //convert all utf-8 to ascii
    
    $soapClient = new SoapClient("http://www.enderminh.com/webservices/VietnameseConversions.asmx?WSDL"); 

    $ap_param = array('message'     =>    implode('[%;%]',array_values($videoIdsTitlesInserted))); 
    $info = $soapClient->__call("UnicodeHTMLToUnicode", array($ap_param)); 
    
    $ap_param = array('message'     =>    $info->UnicodeHTMLToUnicodeResult); 
    $info = $soapClient->__call("UnicodeToASCII", array($ap_param));  
    
    $titles=explode('[%;%]',$info->UnicodeToASCIIResult);
    
    foreach (array_keys($videoIdsTitlesInserted) as $videoId)
    {
        print "update title_ascii=$videoIdsTitlesInserted[$videoId]\n";
        $video->update('title_ascii',mysqli_real_escape_string($db,$videoIdsTitlesInserted[$videoId]), "videoid=$videoId");
    }
?>