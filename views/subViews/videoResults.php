                    <div class="searchResults">
                        <?php
                            if(isset($VIEW_VARS['videoList']) && is_array($VIEW_VARS['videoList'])) {
                                foreach($VIEW_VARS['videoList'] as $video) {
                        ?>
                                    <div class="videoList">
                                        <a href="<?php print $VIEW_VARS['siteUrl'] . '/?action=episode&videoId=' . $video['videoid'];?>">
                                            <img src="<?php print '.' . $video['thumbnail']; ?>"/>
                                            <p class="title"><?php print $video['title_ascii'];?></p>
                                        </a>
                                    </div>
                        <?php
                                }
                            }
                        ?>
                    </div>