        <?php $path = osc_base_url().'/oc-content/plugins/'.  osc_plugin_folder(__FILE__); ?>
        <div class="votes_stars">
            <?php if( $vote['can_vote'] ) { ?>
            <div class="votes_vote">
                <div class="votes_txt_vote"><?php _e('Vote', 'ads_rating');?></div>
                <div class="votes_star">
                    <span id="">
                        <a href="#" rel="nofollow" title="<?php _e('Without interest', 'ads_rating');?>" class="aPs vote1"></a>
                        <a href="#" rel="nofollow" title="<?php _e('Uninteresting', 'ads_rating');?>" class="aPs vote2"></a>
                        <a href="#" rel="nofollow" title="<?php _e('Interesting', 'ads_rating');?>" class="aPs vote3"></a>
                        <a href="#" rel="nofollow" title="<?php _e('Very interesting', 'ads_rating');?>" class="aPs vote4"></a>
                        <a href="#" rel="nofollow" title="<?php _e('Essential', 'ads_rating');?>" class="aPs vote5"></a>
                    </span>
                </div>
                <img width="1" height="19" alt="" src="<?php echo $path; ?>/img/ico_separator.gif">
            </div>
            <?php } ?>
            <div class="votes_results">
                <span style="float:left; padding-right: 4px;"><?php _e('Result', 'ads_rating');?>  </span>
                <?php 
                    $avg_vote = $vote['vote'];
                ?>
                <img title="<?php _e('Without interest', 'ads_rating');?>" src="<?php voting_star(1, $avg_vote); ?>">
                <img title="<?php _e('Uninteresting', 'ads_rating');?>" src="<?php voting_star(2, $avg_vote); ?>">
                <img title="<?php _e('Interesting', 'ads_rating');?>" src="<?php voting_star(3, $avg_vote); ?>">
                <img title="<?php _e('Very interesting', 'ads_rating');?>" src="<?php voting_star(4, $avg_vote); ?>">
                <img title="<?php _e('Essential', 'ads_rating');?>"  src="<?php voting_star(5, $avg_vote); ?>"> 
                <span style="float:left; padding-right: 4px; padding-left: 4px;"><?php echo $vote['total'];?> <?php _e('votes', 'ads_rating');?></span>
            </div>
        </div>