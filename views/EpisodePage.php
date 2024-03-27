<?php include_once("HeaderINC.php");?>
                <?php if( isset($VIEW_VARS['searchResults'][0]) ) { ?>
                    <div>
                        <span class="episodeTitle"><?php print $VIEW_VARS['searchResults'][0]['title_ascii'] . " - episode ";?></span>
                        <span class="episodeNumber"><?php print $VIEW_VARS['currentActiveEpisode'];?></span>
                        
                    </div>
                    <div class="player">
                        <embed align="middle" height="370" width="630" 
                            allowscriptaccess="sameDomain" menu="true"
                            wmode="transparent" allowfullscreen="true" 
                            quality="high" bgcolor="#ffffff" 
                            src="<?php print trim($VIEW_VARS['searchResults'][0]['embeddedlink']);?>" 
                            type="application/x-shockwave-flash" 
                            pluginspage="http://www.macromedia.com/go/getflashplayer">
                    </div>
                    <div class='movieList'>
                        <div class="topBox"></div>
                        <div class="midBox">
                            <ul>
                                <?php $i=1; ?>
                                <?php foreach($VIEW_VARS['searchResults'] as $video){ ?>
                                    <?php 
                                        if( $VIEW_VARS['currentActiveEpisode'] == $i ) {
                                            print '<li class="midBoxActive">';
                                            $i++;
                                        }
                                        else {
                                            print '<li>';
                                        }
                                        
                                        $episodeName = '';
                                        
                                        if($video['part']){ $episodeName = "$video[episode] - $video[part]"; }
                                        else {$episodeName = "$video[episode]";}
                                    ?>
                                        <a href="#" onclick="switchTape('<?php print trim($video['embeddedlink']);?>', this);return false;">
                                            <?php print $episodeName; ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                        <div class="bottomBox"></div>
                    </div>
                <?php }
                    else {
                ?>
                    <span class="title">This movie is currently unavailable</span>
                <?php } ?>
                <script type="text/javascript">
                    function switchTape(embeddedLink, anchor) { 
                        
                        $('.midBoxActive').attr('class',"");
                        $(anchor).parent().attr('class','midBoxActive')
                        $('.episodeNumber').html( $(anchor).html() );
                        
                        $('.player').html('');
                        $('.player').html('<embed align="middle" height="370" width="630" allowscriptaccess="sameDomain" menu="true" wmode="transparent" allowfullscreen="true" quality="high" bgcolor="#ffffff" src="' + embeddedLink + '" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer">'); 
                    }
                </script>
                
<?php include_once("FooterINC.php");?>