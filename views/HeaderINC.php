<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <?php
        if(!isset($VIEW_VARS['searchTerm'])) {
            $VIEW_VARS['searchTerm']='search movie...';
        } 
    ?>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <title>Dooma-Video</title>
        <link type="text/css" href="<?php print $VIEW_VARS['siteUrl'];?>/css/index.css" rel="stylesheet">
        <!--[if IE]>
            <link type="text/css" href="<?php print $VIEW_VARS['siteUrl'];?>/css/index_ie.css" rel="stylesheet">
        <![endif]-->
        
        <script type="text/javascript" src="<?php print $VIEW_VARS['siteUrl'];?>/lib/js/jquery.1.5.1.min.js"></script>
        <script type="text/javascript" src="<?php print $VIEW_VARS['siteUrl'];?>/lib/js/jqueryAutocomplete/jquery.autocomplete.js"></script>
        <script type="text/javascript" src="<?php print $VIEW_VARS['siteUrl'];?>/js/index.js"></script>
        
        <script type="text/javascript">
            //global js vars
            siteUrl='<?php print $VIEW_VARS["siteUrl"]; ?>';
            searchTerm='<?php print $VIEW_VARS["searchTerm"]; ?>';
            
            $(document).ready(function() {
                init();                
            });
        </script>
    </head>
    <body>
        <div class="wrapper">
            <div class="header">
                <div class="logo"><a href="<?php print $VIEW_VARS['siteUrl'] ?>"><span>Welcome to video</span></a></div>
                <div class="search">
                    <form action="<?php print $VIEW_VARS['siteUrl']; ?>/index.php?action=searchResult" method="post">
                        <input type="text" class="searchInput" name="searchText" 
                            value="<?php print $VIEW_VARS['searchTerm']; ?>"/>
                        <input type="submit" class="searchButton" value=""/>
                    </form>
                </div>
                <div class="account">
                    <a href="<?php print $VIEW_VARS['siteUrl']; ?>/?action=register" class="registerLink">register</a> &nbsp;|&nbsp; 
                    <a href="<?php print $VIEW_VARS['siteUrl']; ?>/?action=login" class="loginLink">login</a>
                </div>
            </div>
            <div class="headerMainBreaker"></div>
            <div class="main">
                <div class="navigator">
                    <ul>
                        <li class="homeTab"><a href="<?php print $VIEW_VARS['siteUrl'] ?>"><span>&nbsp;</span></a></li>
                        <li class="categoriesTab"><a href="<?php print $VIEW_VARS['siteUrl'] ?>"><span>&nbsp;</span></a></li>
                    </ul>
                </div><br/>
                <div class="content">