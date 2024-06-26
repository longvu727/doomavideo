<?php
    //get all 
    set_time_limit(0);
    include('../lib/php/simple_html_dom.php');
    include_once('../doomaconfig/Config.php');
    include_once('../models/Video.php');
    
    $db=Config::getDB();
    $video = new Video($db);
    
    $siteUrl='http://iphim.tv/';
    $thumbNailDir='C:\wamp\www\doomavideo\images\thumbnails';
    $htmlLinks=array('http://iphim.tv/id-1-Phim-Bo.html','http://iphim.tv/id-2-Phim-Le.html');//
    $updateMode=0;
    
    if($argv[1]=='update'){ $updateMode=1;}
    
    $videoIdsTitlesInserted=array();
    
    foreach( $htmlLinks as $htmlLink)
    {
        $html = file_get_html($htmlLink);
        
        $count=0;
        do
        {
            print "\n\n" . $count++ . " page $updateMode\n\n";
            
            $moviePages = array_merge ($html->find('div[class=row1] div'), $html->find('div[class=row2] div'));
            $nextPages = $html->find('div[class=pagenumbers] a[title=Next]');
            $thumbnailLink;
            foreach( $moviePages as $moviePage) 
            {
                if($posterImageLink=$moviePage->find('.thumby')) { $thumbnailLink='http://iphim.tv/'.$posterImageLink[0]->src; }
                else if ($movieItems=$moviePage->find('ul[class=listitem] li'))
                {
                    //get title
                    $anchorTitle=$movieItems[0]->find('a');
                    $TITLE=$anchorTitle[0]->innertext();
                    $TITLE= preg_replace('/\(.*/','',$TITLE);

                    print "Processing $TITLE...<br/>\n";
                    
                    //get total episode
                    preg_match('/\d+\/(\d+)|\d+eps/i', $anchorTitle[0]->innertext(), $TOTALEPISODE);
                    
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
                        $VIDEOID= $video->insert( 
                                            array('videoid', 'title', 'description', 
                                                'image', 'totalepisode', 'subtitle', 
                                                'language', 'category', 'thumbnail', 'large_thumbnail'),
                                            array('NULL',$TITLE, $DESCRIPTION,
                                                $IMAGE, $TOTALEPISODE, $SUBTITLE,
                                                $LANGUAGE, $CATEGORY, $THUMBNAIL, $LARGE_THUMBNAIL)
                                        );
                        $videoIdsTitlesInserted[$VIDEOID]= $TITLE;
                    }
                    
                    //download images
                    if(!empty($THUMBNAIL))
                    {
                        $imageType=end(explode('.',$THUMBNAIL));

                        if(!file_exists("$thumbNailDir/$VIDEOID.$imageType"))
                        { 
                            print "download image: $THUMBNAIL\n"; 
                            file_put_contents("$thumbNailDir/$VIDEOID.$imageType", file_get_contents($THUMBNAIL));
                            $video->update(array('thumbnail'),array("/images/thumbnails/$VIDEOID.$imageType"),"videoid=$VIDEOID");
                        } 
                    }
                    
                    //process links
                    $episodeLinks = $episodePage->find('div[id=playlist] ul li a');
                    
                    $insertLinkQuery="insert into links(linkid, videoid, episode, part, totalpart, embeddedlink) values ";
                    $linkInsertFlag=0;
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
                            $insertLinkQuery.="(NULL,'$VIDEOID','$EPISODENUM','$PART','$TOTALPART','$EMBEDDEDLINK'),";
                            $linkInsertFlag=1;
                        }
                        //else{print "$EMBEDDEDLINK existed\n";}
                    }
                    
                    //has insert query
                    if($linkInsertFlag)
                    { 
                        $insertLinkQuery=trim($insertLinkQuery,',');
                        $updateVideoTimeQuery="update video set update_time=now() where videoid=$VIDEOID";
                        $db->query($insertLinkQuery);
                        $db->query($updateVideoTimeQuery);
                    }
                    
                    $episodePage->clear(); 
                    unset($episodePage);
                }
            }
            $html->clear(); 
            unset($html);
            
            if(isset($nextPages[0])){ $html=file_get_html($nextPages[0]->href);}
            else {$html= false;}
            
            if($updateMode){break;}
                      
        }while($html);
    }
    
    //convert all utf-8 to ascii
    
    $soapClient = new SoapClient("http://www.enderminh.com/webservices/VietnameseConversions.asmx?WSDL"); 

    $ap_param = array('message'     =>    implode('[%;%]',array_values($videoIdsTitlesInserted))); 
    $info = $soapClient->__call("UnicodeHTMLToUnicode", array($ap_param)); 
    
    $ap_param = array('message'     =>    $info->UnicodeHTMLToUnicodeResult); 
    $info = $soapClient->__call("UnicodeToASCII", array($ap_param));  
    
    $titles=explode('[%;%]',$info->UnicodeToASCIIResult);
    $videoIds=array_keys($videoIdsTitlesInserted);
    
    if(count($titles)==count($videoIds))
    {
        for($i=0;$i<count($videoIds);$i++)
        {
            print "update title_ascii=$titles[$i]\n";
            $video->update(array('title_ascii'),array(mysqli_real_escape_string($db,$titles[$i])), "videoid=$videoIds[$i]");
        }
    }
    else{print "Number for titles and videoids are not equal";}
?>