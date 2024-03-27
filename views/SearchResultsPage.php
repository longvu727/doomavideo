<?php include_once("HeaderINC.php");?>
                    <?php include_once("$VIEW_VARS[viewRoot]/subViews/videoResults.php"); ?>
                    
                    <div class="paginator">
                        <ul>
                            <?php if ($VIEW_VARS['previousPage']) {?> <li> <?php print "<a href=\"$VIEW_VARS[siteUrl]/?action=searchResult&searchText=".urlencode($VIEW_VARS['searchTerm'])."&page=$VIEW_VARS[previousPage]\">&lt;</a>"?> </li> <?php }?>
                            <?php for($i = 1; $i<=$VIEW_VARS['totalPage']; $i++) {?><li> <?php print "<a href=\"$VIEW_VARS[siteUrl]/?action=searchResult&searchText=".urlencode($VIEW_VARS['searchTerm'])."&page=$i\">$i</a>"?> </li><?php }?>
                            <?php if ($VIEW_VARS['nextPage']) {?> <li> <?php print "<a href=\"$VIEW_VARS[siteUrl]/?action=searchResult&searchText=".urlencode($VIEW_VARS['searchTerm'])."&page=$VIEW_VARS[nextPage]\">&gt;</a>"?></li> <?php }?>
                        </ul>
                    </div>
<?php include_once("FooterINC.php");?>