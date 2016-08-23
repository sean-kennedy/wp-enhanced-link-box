<?php
/**
 * @link              https://seankennedy.com.au/
 * @since             1.0.0
 * @package           WP_Enhanced_Link_Box
 *
 * @wordpress-plugin
 * Plugin Name:       WP Enhanced Link Box
 * Plugin URI:        https://github.com/sean-kennedy/wp-enhanced-link-box/
 * Description:       Enhances the functionality of the WP TinyMCE link button. 
 * Version:           1.0.0
 * Author:            Sean Kennedy
 * Author URI:        https://seankennedy.com.au/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-enhanced-link-box
 * Domain Path:       /languages
 */
 
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin update checker
 */
require 'plugin-update-checker/plugin-update-checker.php';

$plugin_updater = PucFactory::getLatestClassVersion('PucGitHubChecker');

$client_admin_update_checker = new $plugin_updater(
    'https://github.com/sean-kennedy/wp-enhanced-link-box/',
    __FILE__,
    'master'
);

/**
 * Skip inline link editor and open full editor when link icon is clicked
 *
 * @since 1.0.0
 */
add_filter('mce_external_plugins', 'wp_enhanced_link_box_tinymce_plugin');
 
function wp_enhanced_link_box_tinymce_plugin($plugins) {
    
	$plugins['wplinkpre45'] = plugins_url('includes/remove_inline_link.js', __FILE__);
	
	return $plugins;
	
}

/**
 * Add media items to link query
 *
 * @since 1.0.0
 */
add_filter('wp_link_query_args', 'wp_enhanced_link_box_add_media', 10, 1);

function wp_enhanced_link_box_add_media($query) {
    
    if (is_admin()) {
        $query['post_status'] = array('publish', 'inherit');
    }
    
    return $query;
    
}

/**
 * Filter link query by mime type
 *
 * @since 1.0.0
 */
add_filter('wp_link_query', 'wp_enhanced_link_box_add_media_filter', 10, 2);

function wp_enhanced_link_box_add_media_filter($results, $query) {
    
    $allowed_mime_types = array('application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    
    foreach ($results as $result_key => &$result) {
        
        if ('Media' === $result['info']) {
            
            $mime_type = get_post_mime_type($result['ID']);
            
            if ($mime_type == null || in_array($mime_type, $allowed_mime_types)) {
                $result['permalink'] = wp_get_attachment_url($result['ID']);
            } else {
                unset($results[$result_key]);
            }
            
        }
        
    }
    
    return $results;
    
}