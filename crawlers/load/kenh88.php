<?php
    include_once ('../../doomaconfig/Config.php');
    $db= Config::getDB();
    
    $DOOMA_GLOBALS= Config::getGlobals();
    $DOOMA_GLOBALS = array(
        "siteRoot" => realpath( dirname(dirname(dirname(__FILE__))) ),
    );
    
    include_once("$DOOMA_GLOBALS[siteRoot]/models/Video.php");
    include_once("$DOOMA_GLOBALS[siteRoot]/models/Links.php");
    
    function trackXmlTreeDepth( &$nodesLevel, $name, $xmlReader ) {
        if ( $xmlReader->nodeType == XMLReader::ELEMENT ) {
            $nodesLevel[$name]++;
            return "open tag";
        } 
        else {
            $nodesLevel[$name]--;
            return "close tag";
        }
    }
    
    $_nodesLevelIntepreter = array(
        "link" => array( 
            1 => "video",
            2 => null,
            3 => "links"
        ),
    );
    $_nodesLevel = array(
        "link" => 0,
        "href" => 0,
        "name" => 0,
        "title" => 0,
        "thumbnail" => 0,
        "embeddedlink" => 0,
    );
    
    $videoDB =  new Video( $db );
    $linksDB =  new Links( $db );
    
    $videoId = null;
    $data = array();
    $dataStack = array();
    $xml = new XMLReader();
    $xml->open('../links2.xml');
    while( $xml->read() ) {
        print $xml->name . " " . $xml->value . "\n";
        
        switch( $xml->name ) {
            case 'link':
                $tagType = trackXmlTreeDepth( $_nodesLevel, 'link', $xml );
                print "node_level\n" . $_nodesLevel['link'];
                print_r($data);
                
                if( $tagType == 'open tag' && $_nodesLevel['link'] == 2 ) {
                    print "Saving video $data[title] ...\n";
                    //save video
                    $values = array( 
                        null, $data['title'], 'title_ascii', 
                        'description', 'image', 'totalepisode', 
                        'subtitle', 'language', 'category', 'category_ascii', 
                        'thumbnail', 'large_thumbnail', 'now()', 
                        1, //active
                    );
                    $videoId = $videoDB->insert( $videoDB->getColumns(), $values );
                    //save image
                    print "Downloading image $data[thumbnail] ...\n";
                    $fileType = array_pop( explode( '.', $data['thumbnail'] ) );
                    $imageContent = file_get_contents( $data['thumbnail'] );
                    $imageFileName = $DOOMA_GLOBALS['siteRoot']."/images/thumbnails/$videoId.$fileType";
                    file_put_contents( $imageFileName, $imageContent);
                    //update image link
                    $videoDB->update( array('thumbnail'), array("./images/thumbnails/$videoId.$fileType"), "videoid=$videoId" );
                }
                elseif( $tagType == 'close tag' && $_nodesLevel['link'] == 2 ) {
                    //save links
                    print "Saving link $data[name]\n";
                    preg_match('/(\d*)([a-zA-Z]*)/', $data['name'], $matches);
                    //find $episode and $part
                    $episode = 0;
                    $part = 0;
                    $totalpart = 0;
                    $embeddedlink = '';
                    if( count($matches) == 3 ) {
                        list( $episode, $part ) = array_slice( $matches, 1, 2 );
                        $part = ord( strtoupper($part) ) - 64; //if 'A' then 65-64 = 1
                    }
                    else {
                        $episode = $data['name'];
                    }
                    //find $embeddedlink
                    preg_match("/new SWFObject\('(.*?)'\,'/", $data['embeddedlink'], $matches);
                    if( count($matches) == 2 ) {
                        $embeddedlink = $matches[1];
                    }
                    else {
                        $embeddedlink = $data['embeddedlink'];
                    }
                    
                    $value = array(
                        null, $videoId, $episode, 
                        $part, $totalpart, $embeddedlink, 
                        'now()'
                    );
                    $linksDB->insert( $linksDB->getColumns(), $value );
                }
                elseif( $tagType == 'close tag' ) {
                    unset( $data );
                    $data = array();
                }
            break;
            case 'href':
                $tagType = trackXmlTreeDepth( $_nodesLevel, 'href', $xml );
                if( $tagType == 'open tag' ) {
                    $data['href'] = $xml->readInnerXML();
                }
            break;
            case 'name':
                $tagType = trackXmlTreeDepth( $_nodesLevel, 'name', $xml );
                if( $tagType == 'open tag' ) {
                    $data['name'] = $xml->readInnerXML();
                }
            break;
            case 'title':
                $tagType = trackXmlTreeDepth( $_nodesLevel, 'title', $xml );
                if( $tagType == 'open tag' ) {
                    $data['title'] = $xml->readInnerXML();
                }
            break;
            case 'thumbnail':
                $tagType = trackXmlTreeDepth( $_nodesLevel, 'thumbnail', $xml );
                if( $tagType == 'open tag' ) {
                    $data['thumbnail'] = $xml->readInnerXML();
                }
            break;
            case 'embeddedlink':
                $tagType = trackXmlTreeDepth( $_nodesLevel, 'embeddedlink', $xml );
                if( $tagType == 'open tag' ) {
                    $data['embeddedlink'] = $xml->readInnerXML();
                }
            break;
        }
    }
    
?>