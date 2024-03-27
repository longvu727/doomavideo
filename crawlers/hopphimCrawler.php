<?php
    //get all 
    set_time_limit(0);
    include('../lib/php/simple_html_dom.php');
    include_once('../doomaconfig/Config.php');
    include_once('../models/CrawlerVideo.php');
    
    $db=Config::getDB();
    $crawlerVideo = new CrawlerVideo($db);
    
    $siteUrl='http://www.hopphim.com';
    $thumbNailDir='C:\wamp\www\doomavideo\images\thumbnails';
    $htmlLinks=array('http://www.hopphim.com/phim-bo');
    $updateMode=0;
    
    foreach( $htmlLinks as $htmlLink) {
        
        $html = file_get_html($htmlLink);
        $pageNum=0;
        
        do {
            $pageNum++;
            $contentpaneopenTables=$html->find('table[class=contentpaneopen]');
            
            print "***************** PAGE $pageNum *****************\n";   
            
            for( $i=0;$i<count($contentpaneopenTables);$i+=2) {
                
                $titleTable=$contentpaneopenTables[$i];
                $contentTable=$contentpaneopenTables[$i+1];
                
                $titleLinkHtml=$titleTable->find('a[class=contentpagetitle]');
    
                $TITLE= isset($titleLinkHtml[0])?trim($titleLinkHtml[0]->innertext):''; 
                $VIDEOLINK = isset($titleLinkHtml[0])?$siteUrl . $titleLinkHtml[0]->href:'';
                $SUBTITLE='';
                $DUB='';
                if(!empty($VIDEOLINK)) {
                    
                    $videoHtml = file_get_html($VIDEOLINK);
                    //Link Online English Sub - Clicks Vào Ads Để Có Phim Xem Nhanh Hơn
                    
                    $videoContentPs=$videoHtml->find('.contentpaneopen tr td[valign=top] p');
                    
                    foreach ($videoContentPs as $videoContentP) {
                        
                        $linkSections=$videoContentP->find('span');
                        if(isset($linkSections[0]) && preg_match('/Link Online/',$linkSections[0]->plaintext)) {
                            
                            $matches=array();
                            $pattern='/Link Online\s+(\w+)?( sub)?/';
                            preg_match($pattern, trim($linkSections[0]->plaintext), $matches);
                            
                            if(isset($matches[2]) && $matches[2] == 'sub'){$SUBTITLE=$matches[1];}
                            else if(isset($matches[1])){$DUB=$matches[1];}
                            
                            //get links here
                            $episodeLinks=$videoContentP->find('a');
                            foreach($episodeLinks as $episodeLink) {
                                
                                $matches=array();
                                $pattern='/(\d+)([a-zA-Z]+)?/';
                                preg_match($pattern, trim($episodeLink->plaintext), $matches);
                                $EPISODENUM=isset($matches[1])?intval($matches[1]):0;
                                $EPISODEPART=0;

                                if(isset($matches[2])){$EPISODEPART=ord(strtolower($matches[2]))-96;}
                                
                                print "$EPISODENUM-$EPISODEPART\n";
                                //insert links to db here
                            }
                            
                        }
                        //if(isset($span[0]) && preg_match($pattern, $span[0]->plaintext, $matches) );
                    }
                    
                    $videoHtml->clear();
                    unset($videoHtml);
                }
                print "processing $TITLE $VIDEOLINK...\n";
                
                $titleTable->clear();
                unset($titleTable);
                
                $categoryLinks = $contentTable->find('tr td span a');
                $CATEGORY=isset($categoryLinks[0])?trim($categoryLinks[0]->innertext):'';
                $COUNTRY=isset($categoryLinks[1])?trim($categoryLinks[1]->innertext):'';
    
                $THUMBNAILLINK = $contentTable->find('tr td table img');
                $THUMBNAILLINK=isset($THUMBNAILLINK[0])?$THUMBNAILLINK[0]->getAttribute('src'):'';
    
                $DESCRIPTION='';
                $DESCRIPTION_P= $contentTable->find('tr td table p');
                foreach ($DESCRIPTION_P as $desc) {
                    $desc=trim($desc->plaintext);
                    if(!empty($desc) && $desc != 'Nội Dung Chính:') {
                        $DESCRIPTION=$desc;
                        break;
                    }
                }
    
                /*$crawlerVideo->insert(
                                        array('crawler_video_id', 'title', 'category', 
                                                'country', 'image_url', 
                                                'video_link', 'description'), 
                                        array('NULL', $TITLE, $CATEGORY,
                                                $COUNTRY, $THUMBNAILLINK, 
                                                $VIDEOLINK, $DESCRIPTION)
                                    );*/
            }
            
            $nextPage=$html->find('.pagination a[title=Next]');
            
            $html->clear(); 
            unset($html);    
            
            exit();
            
            if(isset($nextPage[0])){ $html=file_get_html($siteUrl.$nextPage[0]->href);}
            else {$html= false;}
            
        }while($html);
        
    }
?>