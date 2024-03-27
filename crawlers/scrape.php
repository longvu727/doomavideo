<?php
    //error_reporting(0);
    //ini_set('display_errors', 0);

    include_once ('../doomaconfig/Config.php');
    $db= Config::getDB();
    $DOOMA_GLOBALS= Config::getGlobals();
    $DOOMA_GLOBALS["siteRoot"] = realpath( dirname(dirname(__FILE__)) );
    
    include_once ('../models/CrawlerManager.php');
    include_once ('../models/ContentScrapes.php');
    include_once ('../lib/php/simple_html_dom.php');
    include_once ('./ScrapeLib.php');
    
    function scrapeContents ( $db, $crawlerManagerId, $url, $domTree, $xmlRoot ) {
        static $depth=0;
        $depth++;
        print "Processing crawler $crawlerManagerId, depth $depth, url $url start\n";
        
        $crawlerManager = new CrawlerManager( $db );
        $crawlerNode = $crawlerManager->findCrawlerManagerById( $crawlerManagerId );
        
        $contentScrapes = new ContentScrapes( $db );
        $scrapes = $contentScrapes->find( array( "crawler_manager_id" => $crawlerNode["crawler_manager_id"] ) );
        
        if( !empty($url) ) {
            $crawlerNode['url'] = $url;//the url in crawlerNode is just one element
        }
        
        //get html for selector
        $html = file_get_html( $crawlerNode['url'] );
        $html = $html->root;
        $html = $html->find($crawlerNode['selector']);
        $html = array_shift( $html );
        
        if( empty($html) ) {
            print "empty\n";
            return;
        }
        
        $scrapeResults= array();
        if( !empty($scrapes) ) {
            foreach( $scrapes as $row ) {
                
                $scrapeResult = $html->find( $row["selector"] );
                
                if( !empty($scrapeResult) ) {
                    $scrapeLib = new ScrapeLib();
                    $r = $scrapeLib->getContent($row['scrape_command'], $scrapeResult[0]);
                    $scrapeResults[implode('-',$row)] = $r;
                    //$row['column_name'], scrapecontents
                    $tempNode = $domTree->createElement( $row['column_name'], $r );
                    $xmlRoot->appendChild($tempNode);
                }
            }
        }
        print_r($scrapeResults);
                
        //if sub_id and sub page selector
        if( !empty($crawlerNode['sub_page_url_selector']) && $crawlerNode["sub_id"] != 0 ) {
            $subPages = $html->find( $crawlerNode['sub_page_url_selector'] );
            print "sub page count: " . count( $subPages ) . "\n";
            if( !empty($subPages) ) {
                foreach( $subPages as $subPage ) {
                    $href = $subPage->getAttribute('href');
                    $href_innertext = trim( strip_tags($subPage->innertext()) );
                    if( !$href ) {
                        continue;
                    }
                    if( preg_match("/^\//", $href) ) {
                        $parsed_url = parse_url( $crawlerNode["url"] );
                        $href = "$parsed_url[scheme]://$parsed_url[host]" . $href;
                    }
                    
                    //create a link node with name and href sub node
                    $linkNode = $domTree->createElement("link_$depth");
                    $hrefNode = $domTree->createElement("href", $href);
                    $nameNode = $domTree->createElement("name", $href_innertext);
                    
                    $linkNode->appendChild($hrefNode);
                    $linkNode->appendChild($nameNode);
                    //append link node to root node or parent node
                    $xmlRoot->appendChild($linkNode);
                    print "\n************* $href_innertext -> $href *************\n";
                    //passing linkNode as the parent node to the subsequence calls
                    scrapeContents( $db, $crawlerNode["sub_id"], $href, $domTree, $linkNode );
                    
                    if( $depth == 3 ) { 
                        file_put_contents("links", $domTree->saveXML($xmlRoot), FILE_APPEND );
                    //    return; 
                    }
                    $depth--;
                    
                }
            }
            else {
                return;
            }
        }
        
        print "Processing crawler $crawlerManagerId end\n";
    }
    
    function getRootNode( $crawlerTree ) {
        foreach( $crawlerTree as $crawlerNode ) {
            if( $crawlerNode['crawler_manager_id'] == $crawlerNode['root_id'] ) {
                return $crawlerNode;
            }
        }
    }
    
    //------------------------- MAIN -------------------------
    $crawlerManagerRootId = 15;
    
    $domtree = new DOMDocument('1.0', 'UTF-8');
    $domtree->formatOutput=1;
    $xmlRoot = $domtree->createElement("xml");
    $xmlRoot = $domtree->appendChild($xmlRoot);
    
    $crawlerManager = new CrawlerManager( $db );
    $crawlerNode = $crawlerManager->findCrawlerManagerById( $crawlerManagerRootId );
    
    //get html for selector
    $html = file_get_html( $crawlerNode['url'] );
    $html = $html->root;
    $html = $html->find($crawlerNode['selector']);
    $html = array_shift( $html );
    
    $iterator_pages = array();
    if( $crawlerNode['iterator_selector'] ) {
        $parsedUrl = parse_url($crawlerNode['url']);
        
        $iterator_pages = $html->find( $crawlerNode['iterator_selector'] );
        foreach( $iterator_pages as $page ) {
            if( $page->innerText() < 11 ) {
                continue;
            }
            $url = "$parsedUrl[scheme]://$parsedUrl[host]/" . $page->getAttribute('href');
            print "executing $url ...\n";
            //scrapeContents( $db, $crawlerManagerRootId, $url, $domtree, $xmlRoot );
        }
    }
    else {
        scrapeContents( $db, $crawlerManagerRootId, 0, $domtree, $xmlRoot );
    }

    print $domtree->save('links.xml');
?>