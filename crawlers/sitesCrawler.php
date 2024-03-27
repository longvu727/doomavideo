<?php
            include_once ('../doomaconfig/Config.php');
            include_once ('../models/CrawlerManager.php');
            include_once ('../models/ContentScrapes.php');
            $db= Config::getDB();
            $DOOMA_GLOBALS= Config::getGlobals();
            
            //video columns
            $video_columns = array(
                        "title",
                        "description",
                        "totalepisode",
                        "subtitle",
                        "thumbnail",
                    );
            
            $html;
            $cssSelectorHtml;
            $iteratorCssSelectorHtml;
            $nextCrawlerPage;
            $nextUrlPage;
            $subPageUrl;
            $familyTree = array();
            $scrapeResults = array();

            $name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
            $url = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';
            $cssSelector = isset($_REQUEST['cssSelector']) ? $_REQUEST['cssSelector'] : '';
            $iteratorCssSelector = isset($_REQUEST['iteratorCssSelector']) ? $_REQUEST['iteratorCssSelector'] : '';
            $iteratorRegex = isset($_REQUEST['iteratorRegex']) ? $_REQUEST['iteratorRegex'] : '';
            $subPageUrlCssSelector = isset($_REQUEST['subPageUrlCssSelector']) ? $_REQUEST['subPageUrlCssSelector'] : '';
            $subPageUrl = isset($_REQUEST['subPageUrl']) ? $_REQUEST['subPageUrl'] : '';
            $currentId = isset($_REQUEST['currentId']) ? $_REQUEST['currentId'] : 0;
            $parentId = isset($_REQUEST['parentId']) ? $_REQUEST['parentId'] : 0;
            $rootId = isset($_REQUEST['rootId']) ? $_REQUEST['rootId'] : 0;
            $subId = isset($_REQUEST['subId']) ? $_REQUEST['subId'] : 0;
            
            $contentScrapes = new ContentScrapes( $db );
            
            if( isset($_POST["ajaxType"]) ) {
                $returnStr;
                switch( $_POST["ajaxType"] ) {
                    case "delete_content_scrape":
                    	if( !isset($_POST['content_scrape_id']) ) {
                    	    $returnStr = "Content scrape id is not provided";
                        }
                        else {
                        	$contentScrapes =  new ContentScrapes( $db );
                            $contentScrapes->delete( $_POST['content_scrape_id'] );
                            $returnStr = "Content scrape id $_POST[content_scrape_id] is deleted!";
                        }
                    break;
                }
                print $returnStr;
                return;
            }
            
            if( isset($_POST['submit']) ) {
                set_time_limit(0);

                include_once('../lib/php/simple_html_dom.php');
                include_once('./ScrapeLib.php');
                
                $html = file_get_html($url);
                $cssSelectorHtml = $html->root;//default node
                
                if( !empty($cssSelector) ) {
                    
                    $cssSelectorHtml = $html->find( $cssSelector );
                    $contentScrapesRows = $contentScrapes->find( array( "crawler_manager_id" => $currentId ) );
            
                    if( !empty($contentScrapesRows) ) {
                        foreach( $contentScrapesRows as $row ) {
                            
                            $scrapeResult = $cssSelectorHtml[0]->find( $row["selector"] );
                            
                            if( !empty($scrapeResult) ) {
                                $scrapeLib = new ScrapeLib();
                                $scrapeResults[implode('-',$row)] = $scrapeLib->getContent( $row['scrape_command'], $scrapeResult[0] );
                            }
                        }
                    }
                    
                    //show only one link
                    if( !empty($subPageUrlCssSelector) ) {
                        foreach( $cssSelectorHtml as $element ) {
                            $subPageUrlHtml = $element->find( $subPageUrlCssSelector );
                            foreach( $subPageUrlHtml as $hrefHtml ) {
                                if( $hrefHtml->getAttribute('href') ) {
                                    $subPageUrlHtml[0] = $hrefHtml;
                                    break 2;
                                }
                            }
                        }
                        $urlParsed = parse_url( $url );
                        if( stripos($subPageUrlHtml[0]->getAttribute( 'href' ), 'http') === false ) {
                            $subPageUrl = $urlParsed['scheme'] . "://" . $urlParsed['host'] . $subPageUrlHtml[0]->getAttribute( 'href' );
                        }
                        else {
                            $subPageUrl = $subPageUrlHtml[0]->getAttribute( 'href' );
                        }
                    }
                }
                
                //searching for the next page, assumed that we don't have next page link
                if( !empty($iteratorCssSelector) ) {
                    $pageIteratorLinkList = $html->find( $iteratorCssSelector );
                    
                    /*if there is no iteratorLink, it must be the first page
                     * if there is, search through the paginator links and find the position of iteratorLink then get the next link
                     * */     
                    
                    if( !empty($iteratorRegex) && !empty($url) ) {
                        preg_match("/$iteratorRegex/", $url, $matches);
                        $iteratorNum = $matches[1];
                        //next page
                        $iteratorNum++;
                        $nextUrlPage = stripslashes( $iteratorRegex );//preg_replace('\', $iteratorNum, $iteratorRegex);
                        $nextUrlPage = preg_replace('/\(d\+\)/', $iteratorNum, $nextUrlPage);
                        
                    }
                    else {
                        $iteratorCssSelectorHtml = array_shift( $pageIteratorLinkList );
                        $nextUrlPage = $iteratorCssSelectorHtml->getAttribute('href');
                    }
                    //make next page link including all parameters and next page link from the current page
                    $nextPageParams = $_GET;
                    //$nextPageParams['sources']='';
                    
                    $nextPageParams['url']=$nextUrlPage;
                    $nextPageParams['iteratorLink']=$nextUrlPage;
                    $urlParameterStr = '';
                    
                    foreach( array_keys($nextPageParams) as $key ) {
                        $urlParameterStr .= "$key=" . urlencode($nextPageParams[$key]) . "&";
                    }
                    $nextCrawlerPage = $_SERVER['SCRIPT_NAME'] . "?$urlParameterStr";
                    
                }
                
                //$html->clear();
            }
            else if( isset($_POST['save']) ) {
                $crawlerManager =  new CrawlerManager( $db );
                $contentScrapes =  new ContentScrapes( $db );

                if( $crawlerManager->findCrawlerManagerByName($name) ) {
                    //already exist
                    print "Unable to Save, same name already exists in the system.";
                    return ;
                }
                else {
                    //save crawler amnager
                    $row = array (
                                'name'                    => $name, 
                                'url'                     => $url, 
                                'selector'                => $cssSelector, 
                                'iterator_selector'       => $iteratorCssSelector, 
                                'iterator_regex'          => $iteratorRegex, 
                                'sub_page_url_selector'   => $subPageUrlCssSelector, 
                                'sub_id'                  => 0, 
                                'parent_id'               => $parentId,
                                'root_id'                 => $rootId
                             );
                    $currentId = $crawlerManager->save( $row );
                    //update parent page's sub id if there is one
                    if( $parentId != 0 ) {
                        $updatedColumnsValues = array (
                                            'sub_id'                  => $currentId, 
                                            'crawler_manager_id'      => $parentId
                                          );
                        
                    }
                    else {
                        $rootId=$currentId;
                        $updatedColumnsValues = array (
                                            'root_id'                  => $currentId, 
                                            'crawler_manager_id'      => $currentId
                                          );
                    }
                    $crawlerManager->saveExistingRow( $updatedColumnsValues );
                    
                    if( isset($_REQUEST['tableName']) ) {
                        for( $i = 0  ;  $i < sizeof($_REQUEST['tableName']) ; $i++ ) {
                            $scrapeCommands = array (
                                'table_name'        => $_REQUEST['tableName'][$i],
                                'column_name'       => $_REQUEST['columnName'][$i],
                                'selector'          => $_REQUEST['selector'][$i],
                                'scrape_command'    => $_REQUEST['scrapeCommand'][$i],
                                'crawler_manager_id'=> $currentId,
                            );
                            $contentScrapes->save( $scrapeCommands );
                        }
                    }
                }
            }
            else if( isset($_POST['delete']) ) {
                if( $subId != 0 ) {
                    //already exist
                    print "Cannot delete this page when this page has a sub page.  Please go back delete the sub page first";
                    return ;
                }
                
                $contentScrapes =  new ContentScrapes( $db );
                $crawlerManager =  new CrawlerManager( $db );
                
                $contentScrapes->deleteByCrawlerManagerId( $currentId );
                $crawlerManager->delete( $currentId );
                
                //update parent page's sub id if there is one
                if( $parentId != 0 ) {
                    $updatedColumnsValues = array (
                                        'sub_id'                  => 0, 
                                        'crawler_manager_id'      => $parentId
                                      );
                    $crawlerManager->saveExistingRow( $updatedColumnsValues );
                }
            }
            else if( isset($_POST['update']) ) {
                $crawlerManager =  new CrawlerManager( $db );
                $updatedColumnsValues = array (
                                        'name'                    => $name, 
                                        'url'                     => $url, 
                                        'selector'                => $cssSelector, 
                                        'iterator_selector'       => $iteratorCssSelector, 
                                        'iterator_regex'          => $iteratorRegex, 
                                        'sub_page_url_selector'   => $subPageUrlCssSelector, 
                                        'crawler_manager_id'      => $currentId
                                      );
                $crawlerManager->saveExistingRow( $updatedColumnsValues );
                
                $contentScrapes =  new ContentScrapes( $db );
                if( isset($_REQUEST['tableName']) ) {
                    for( $i = 0  ;  $i < sizeof($_REQUEST['tableName']) ; $i++ ) {
                        $scrapeCommands = array (
                            'table_name'        => $_REQUEST['tableName'][$i],
                            'column_name'       => $_REQUEST['columnName'][$i],
                            'selector'          => $_REQUEST['selector'][$i],
                            'scrape_command'    => $_REQUEST['scrapeCommand'][$i],
                            'crawler_manager_id'=> $currentId,
                        );
                        
                        if( $contentScrapes->find($scrapeCommands) ) {
                            $contentScrapes->saveExistingRow( $scrapeCommands );
                        }
                        else {
                            $contentScrapes->save( $scrapeCommands );
                        }
                    }
                }
            }
            else if( isset($_POST['createSub']) ) {
                $parentId = $currentId;
                $currentId =0 ;
                $name = '';
                $url = $subPageUrl;
                $subPageUrl='';
                $cssSelector = '';
                $iteratorCssSelector = '';
                $iteratorRegex = '';
                $subPageUrlCssSelector='';
                $contentScrapesRows=array();
            }
            
            if( $rootId ) {
                $crawlerManager =  new CrawlerManager( $db );
                $familyTree = $crawlerManager->findFamilyTree( $rootId );
            }
            
            $contentScrapesRows = $contentScrapes->find( array( "crawler_manager_id" => $currentId ) );
            
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <style>
            label {
                float: left;
                width: 250px;
                text-indent: 15px;
            }
            input {
                width: 500px;
            }
            .addScrapeButton {
                width: 100px;
            }
            .submit {
                width: 180px;
            }
            .scrapeCommandTable td {
                margin:0;
                border: 1px solid;
            }
            .scrapeCommandTableInput {
                width: 200px;
            }
            .scrapeCommandTableDelete {
                width: 25px;
                color: red;
            }
            .scrapeCommandTable .deleteTd {
                border: 0px;
            }
            .left {
                float: left;
            }
            .right {
                float: right;
            }
        </style>
        <script type="text/javascript" src="../lib/js/jquery.1.5.1.min.js"></script>
        <script type="text/javascript">
            function deleteScrapeCommand( deleteScrapeCommandButton ) {
                $.ajax({
                    type: "POST",
                    url: "sitesCrawler.php",
                    data: { ajaxType: "delete_content_scrape", content_scrape_id: $(deleteScrapeCommandButton).attr("name") }
                })
                .done( function( msg ) {
                    alert( "Data Saved: " + msg );
                });
                
                $(deleteScrapeCommandButton).parent().parent().remove();
            }
            $(document).ready(function() {
                $('div#addScrape').click( function(){
                    html = '<tr>';
                    html += '<td><input class="scrapeCommandTableInput" type="text" name="tableName[]"/></td>';
                    html += '<td><input class="scrapeCommandTableInput" type="text" name="columnName[]"/></td>';
                    html += '<td><input class="scrapeCommandTableInput" type="text" name="selector[]"/></td>';
                    html += '<td><input class="scrapeCommandTableInput" type="text" name="scrapeCommand[]"/></td>';
                    html += '</tr>';
                    $('table#scrapeCommandTable').append( html );
                });
                
                $('#scrapeCommandTableDelete').click( function() {
                    
                });
            });
            
        </script>
    </head>
    <body>
        
        <div class="left">
            <?php
                foreach( $familyTree as $familyMember ) {
                    if( $currentId == $familyMember["crawler_manager_id"] ) {
                        print "$familyMember[name] &nbsp;>&nbsp;";
                    }
                    else {
                        foreach( array_keys($familyMember) as $key ) {
                            $familyMember[$key] = urlencode( $familyMember[$key] );
                        }
                        $familyTreeLink = "./sitesCrawler.php?name=$familyMember[name]&url=$familyMember[url]&"
                                    . "cssSelector=$familyMember[selector]&iteratorCssSelector=$familyMember[iterator_selector]&"
                                    . "iteratorRegex=$familyMember[iterator_regex]&parentId=$familyMember[parent_id]&currentId=$familyMember[crawler_manager_id]&"
                                    . "subId=$familyMember[sub_id]&subPageUrlCssSelector=$familyMember[sub_page_url_selector]&"
                                    . "rootId=$familyMember[root_id]&submit=Submit";
                        
                        print "<a href=\"$familyTreeLink\"> $familyMember[name] </a>&nbsp;>&nbsp;";
                    }
                }
            ?>
        </div>
        <div class="right">
            <a style="float:right;" href="<?php if( isset($nextCrawlerPage) ) { print $nextCrawlerPage; } ?>"> next page > </a>
        </div><br><br>
        
        <form method="post" action="">
            <h3>Crawler Manager</h3>
            
            <input type="hidden" name="parentId" value="<?php print $parentId; ?>"/>
            <input type="hidden" name="currentId" value="<?php print $currentId; ?>"/>
            <input type="hidden" name="rootId" value="<?php print $rootId; ?>"/>
            <input type="hidden" name="subId" value="<?php print $subId; ?>"/>
            
            <label>Name:  </label>
            <input type="text" name="name" value="<?php print $name; ?>"/><br>
            
            <label>Url:  </label>
            <input type="text" name="url" value="<?php print $url; ?>"/><br>
            
            <label>Find css selector:  </label>
            <input type="text" name="cssSelector" value="<?php print $cssSelector; ?>"/><br>
            
            <label>Iterator css selector:  </label>
            <input type="text" name="iteratorCssSelector" value="<?php print $iteratorCssSelector; ?>"/><br>
            
            <label>Iterator regex:  </label>
            <input type="text" name="iteratorRegex" value="<?php print $iteratorRegex; ?>"/><br>
            
            <label>Sub-page url css selector:  </label>
            <input type="text" name="subPageUrlCssSelector" value="<?php print $subPageUrlCssSelector; ?>"/><br>
            
            <br><br>
            <h3>Scrape Command</h3>
            
            <div id="scrapeCommand">
                <table class="scrapeCommandTable" id="scrapeCommandTable">
                    <tr>
                        <td>table name</td>
                        <td>column name</td>
                        <td>selector</td>
                        <td>scrape command</td>
                    </tr>
                    <?php
                        foreach( $contentScrapesRows as $row ) {
                            print"
                                <tr>
                                    <td><label class=\"scrapeCommandTableInput\">$row[table_name]</label></td>
                                    <td><label class=\"scrapeCommandTableInput\">$row[column_name]</label></td>
                                    <td><label class=\"scrapeCommandTableInput\">$row[selector]</label></td>
                                    <td><label class=\"scrapeCommandTableInput\">$row[scrape_command]</label></td>
                                    <td class=\"deleteTd\"><input class=\"scrapeCommandTableDelete\" id=\"scrapeCommandTableDelete\" onclick=\"deleteScrapeCommand(this)\" name=\"$row[content_scrape_id]\" type=\"button\" value=\"X\"/></td>
                                </tr>
                            ";
                        }
                    
                    ?>
                </table>
            </div>
            <div id="addScrape"><input class="addScrapeButton" type="button" value="Add Scrape +"/></div>
            <div id="scrapeResults">
                <h4>Scrape Results:</h4>
                <?php
                    foreach( array_keys($scrapeResults) as $key ) {
                        print "<i>$key</i>: $scrapeResults[$key]<br/>";
                    }
                ?>
            </div>
            <h3>Constants</h3>
            
            <label>Sub page url (show 1 example):  </label>
            <input type="text" name="subPageUrl" value="<?php if( isset($subPageUrl) ) { print $subPageUrl; } ?>"/><br>
            
            <label>Iterator link(next page):  </label>
            <?php if( isset($nextUrlPage) ) { print $nextUrlPage; } ?><br>
            
            <label>Source:  </label>
            <textarea cols="120" rows="10">
                <?php 
                    if( isset($cssSelectorHtml) ) { 
                        if( is_array( $cssSelectorHtml ) ) {
                            foreach( $cssSelectorHtml as $element ) {
                                print $element->innertext() . "\n";
                            }
                        }
                        else {
                            print_r( $cssSelectorHtml->innertext() );
                        }
                    }
                ?>
            </textarea><br>
            
            <input class='submit' type="submit" name="submit" value="Submit" />
            <input class='submit' type="submit" name="save" value="Save" />
            <input class='submit' type="submit" name="delete" value="Delete" />
            <input class='submit' type="submit" name="update" value="Update" />
            <input class='submit' type="submit" name="createSub" value="Create Sub-Link" />
        </form>
        
        <br>
        <?php 
            if( isset($cssSelectorHtml) ) {
                if( is_array( $cssSelectorHtml ) ) {
                    foreach( $cssSelectorHtml as $element ) {
                        print $element->innertext();
                    }
                }
                else {
                    print_r( $cssSelectorHtml->innertext() );
                }
            }
        ?>
    </body>
</html>