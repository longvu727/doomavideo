<?php
    include_once('doomaconfig/Config.php');
    $DOOMA_GLOBALS=Config::getGlobals();
    $db= Config::getDB();
    
    $VIEW_VARS=
        array(
            'siteUrl'   => ( $_SERVER['HTTP_HOST'] == 'localhost' ) ? 'http://localhost/doomavideo' : 'http://www.dooma.org',
            'viewRoot'  => $DOOMA_GLOBALS['siteRoot'] . "/views",
        );
    $videoPerPage=20;
    
    if(isset($_REQUEST['ajaxRequest']))
    {
        switch ($_REQUEST['ajaxRequest'])
        {
            case 'searchSuggestion':
            	include_once("$DOOMA_GLOBALS[siteRoot]/controller/SearchSuggestion.php");
                $searchSuggestion= new SearchSuggestion();
                $searchSuggestion->printSuggestion($db,$_REQUEST['title']);
            break;
        }
    }
    else if (isset($_REQUEST['action']))
    {
        switch ($_REQUEST['action'])
        {
            case 'searchResult':
            	include_once("$DOOMA_GLOBALS[siteRoot]/controller/SearchResult.php");
                
                $page=isset($_REQUEST['page'])?$_REQUEST['page']:1;
                $searchText=isset($_REQUEST['searchText'])?$_REQUEST['searchText']:'';
                
                $searchResult= new SearchResult();
                $videoResults=$searchResult->searchMovieByTitle($db, $searchText, $videoPerPage, $page);
                $videoCount=$videoResults['resultCount'];
                $VIEW_VARS['videoList']=$videoResults['results'];
                
                $VIEW_VARS['currentPage']=$page;
                $VIEW_VARS['totalPage']=ceil($videoCount/$videoPerPage);
                $VIEW_VARS['searchTerm']=$searchText;
                
                $VIEW_VARS['nextPage']=($VIEW_VARS['currentPage']+1 > $VIEW_VARS['totalPage'])?0:$VIEW_VARS['currentPage']+1;
                $VIEW_VARS['previousPage']=$VIEW_VARS['currentPage']-1;
                
                include_once("$DOOMA_GLOBALS[siteRoot]/views/SearchResultsPage.php");
            break;
            case 'episode':
                include_once("$DOOMA_GLOBALS[siteRoot]/controller/Episode.php");
                $episode = new Episode();
                $results=$episode->getLinksByVideoId($db,$_REQUEST['videoId']);
                
                $VIEW_VARS['searchResults']=$results;
                $VIEW_VARS['currentActiveEpisode']=1;
                
                include_once("$DOOMA_GLOBALS[siteRoot]/views/EpisodePage.php");
            break;
        }
    }
    else 
    {
        include_once("$DOOMA_GLOBALS[siteRoot]/controller/Index.php");
        $index = new Index();
        $VIEW_VARS['videoList']=$index->getLatestVideo($db);
        include_once("$DOOMA_GLOBALS[siteRoot]/views/IndexPage.php"); 
    }
?>