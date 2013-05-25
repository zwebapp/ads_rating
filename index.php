<?php
/*
Plugin Name: Ads Rating
Plugin URI: http://zeyhi.com/works/ads-rating/
Description: Allows the rating of each ads and showing recommended ads for the user.
Version: 1.0.0
Author: Zeus Camua
Author URI: http://zeyhi.com/
Short Name: ads rating
*/

require_once 'AR_Model.php' ;

/* Initialize plugin
---------------------------------------------------------------------------*/

// This is to add the admin link for this page.
osc_add_hook("init", "_init_plugin");


// Initialize plugin informations and other necessary objects.
function _init_plugin() {

  osc_set_preference('ads_rating', 'Ads Rating', 'plugin_name');

  osc_enqueue_style('main_css', osc_base_url() . 'oc-content/plugins/ads_rating/css/main.css');  
}

/* end initialize plugin */


/* Install Ads Rating plugin.
---------------------------------------------------------------------------*/

// This is needed in order to be able to activate the plugin
osc_register_plugin(osc_plugin_path(__FILE__), '_install');


// Set plugin preferences
function _install() {

  AR::newInstance()->import('ads_rating/struct.sql');
  osc_set_preference('ads_rating', '1', 'rating', 'BOOLEAN');

}

/* end Install Ads Rating plugin. */

/* Uninstall Ads Rating plugin.
---------------------------------------------------------------------------*/

// This is a hack to show a Uninstall link at plugins table (you could also use some other hook to show a custom option panel)
osc_add_hook(osc_plugin_path(__FILE__)."_uninstall", '_uninstall');


// Delete plugin preferences
function _uninstall() {
 
  AR::newInstance()->uninstall();
  osc_delete_preference('ads_rating', 'rating');

}

/* end Uninstall Ads Rating plugin. */


/* Admin Configuration
---------------------------------------------------------------------------*/

// This is a hack to show a Configure link at plugins table (you could also use some other hook to show a custom option panel)
osc_add_hook(osc_plugin_path(__FILE__)."_configure", '_admin_configuration');


function _admin_configuration() {

    // Standard configuration page for plugin which extend item's attributes
    osc_plugin_configure_view(osc_plugin_path(__FILE__));

}

/* end Admin Configuration */


/* Adding plugin menu
---------------------------------------------------------------------------*/

// This is to add the admin link for this page.
osc_add_hook("init_admin", "add_menu");

function add_menu() {
 
  if(osc_version() >= 300) {

    osc_add_admin_menu_page( "Ads Rating Options" , osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'conf.php'), 'ads_rating_plugin', 'administrator' );
    // osc_add_admin_submenu_page('ads_rating_plugin', __('Settings', 'voting'), osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'conf.php'), 'voting_plugin_settings', 'administrator');
    // osc_add_admin_submenu_page('voting_plugin', __('Help', 'voting'), osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'help.php'), 'voting_plugin_help', 'administrator');

  } else {

    osc_add_hook('admin_menu', 'voting_admin_menu');

  }

  osc_enqueue_style('admin_css', osc_base_url() . 'oc-content/plugins/ads_rating/css/admin.css');

}

function voting_admin_menu() {

  echo '<h3><a href="#">' . __('Voting options', 'voting') . '</a></h3>
  <ul>
    <li><a href="' . osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'conf.php') . '">&raquo; ' . __('Settings', 'voting') . '</a></li>
    <li><a href="' . osc_admin_render_plugin_url(osc_plugin_folder(__FILE__) . 'help.php') . '">&raquo; ' . __('Help', 'voting') . '</a></li>
  </ul>';

}

/* end Adding plugin menu */


/* Item Detail
---------------------------------------------------------------------------*/

// Show an item special attributes
osc_add_hook('item_detail', 'ads_item_detail');

/*  Show form to rate an item. (itemDetail) */
function ads_item_detail() {

  // Fail-early validation, terminate the function if not allowed in the category.
  if (! osc_is_this_category(osc_get_preference('ads_rating', 'plugin_name'), osc_item_category_id()) ) return;

  // Fail early validation, terminate the function if plugin is disabled.
  if (! osc_get_preference('ads_rating', 'rating') == '1' ) return; 


  $vote['vote']  = AR::newInstance()->getItemAvgRating( osc_item_id() )["vote"];

  $vote['total'] = AR::newInstance()->getItemNumberOfVotes( osc_item_id() )["total"];

  $hash = '';

  if( osc_logged_user_id() == 0 ) {

    $hash = $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'];
    $hash = sha1($hash);

  } 

  else {

    $hash = null;

  }

  $vote['can_vote'] = can_vote( osc_item_id(), osc_logged_user_id(), $hash );

  require 'item_detail.php';

}

/**
 * Check if user can vote an item
 *
 * @param string $itemId
 * @param string $userId
 * @param string $hash
 * @return bool
 */
function can_vote( $itemId, $userId, $hash) {

  if ( $userId == 'NULL' ) {  
    $result = AR::newInstance()->getItemIsRated($itemId, $hash);
  } 
  else {
    $result = AR::newInstance()->getItemIsRated($itemId, $hash, $userId); 
  }

  if ( count($result) > 0 ) return false;
  else return true;
}


/* end Item Detail */


/* Delete item
---------------------------------------------------------------------------*/
osc_add_hook('delete_item', '_item_delete');

// Delete item via itemId
function voting_item_delete($itemId) {
    return ModelVoting::newInstance()->deleteItem($itemId);
}

/* end Delete Item */


    /**************************************************************************
     *                          VOTE ITEMS
     *************************************************************************/


    /**
     * Return layout optimized for sidebar at main web page, with the best items voted with a limit
     *
     * @param int $num number of items
     */
    function echo_best_rated($num = 5)
    {
        if( osc_get_preference('item_voting', 'voting') == 1 ) {
            $filter = array(
                'order'       => 'desc',
                'num_items'   => $num
            );
            $results = get_votes($filter);
            if(count($results) > 0 ) {
                error_log( print_r($results, true) );
                $locale  = osc_current_user_locale();
                require 'set_results.php';
            }
        }
    }

    /**
     * Return an array of item votes with given filters
     * <code>
     * array(
     *          'category_id' => (integer_category_id),
     *          'order'       => ('desc','asc'),
     *          'num_items'   => (integer)
     *      );
     * </code>
     * @param type $array_filters
     * @return array of item votes
     */
    function get_votes($array_filters)
    {
        $category_id = null;
        $order       = 'desc';
        $num         = 5;
        if(isset($array_filters['category_id'])){
            $category_id = $array_filters['category_id'];
        }
        if(isset($array_filters['order'])){
            $order = strtolower($array_filters['order']);
            if( !in_array($order, array('desc', 'asc') ) ){
                $order = 'desc';
            }
        }
        if(isset($array_filters['num_items'])){
            $num = (int)$array_filters['num_items'];
        }

       return ModelVoting::newInstance()->getItemRatings($category_id, $order, $num);
    }


    /**************************************************************************
     *                          VOTE USERS
     *************************************************************************/

    /**
     * Print star img src
     *
     * @param type $star
     * @param type $avg_vote
     * @return type
     */
    function voting_star($star, $avg_vote)
    {
        $path = osc_base_url().'/oc-content/plugins/'.  osc_plugin_folder(__FILE__);
        $star_ok = $path.'img/ico_vot_ok.gif';
        $star_no = $path.'img/ico_vot_no.gif';
        $star_md = $path.'img/ico_vot_md.gif';

        if( $avg_vote >= $star) {
            echo $star_ok;
        } else {
            $aux = 1+($avg_vote - $star);
            if($aux <= 0){
                echo $star_no;
                return true;
            }
            if($aux >=1) {
                echo $star_no;
            } else {
                if($aux <= 0.5){
                    echo $star_md;
                }else{
                    echo $star_ok;
                }
            }
        }
    }

    /**
     * ADD HOOKS
     */

    //osc_add_hook('item_detail', 'voting_item_detail');
    
    //osc_add_hook('delete_item', 'voting_item_delete');
