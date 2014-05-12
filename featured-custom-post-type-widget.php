<?php
/**
 * Featured Custom Post Type Widget For Genesis
 * @package FeaturedCustomPostTypeWidgetForGenesis
 * @author Jo Waltham
 * @license GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Featured Custom Post Type Widget for Genesis
 * Plugin URI:  http://calliaweb.co.uk/
 * Description: Widget to Display Featured Custom Post Types - uses code from Genesis Featured Post Widget and adds support for custom post types and custom taxonomies
 * Version:     1.1.0
 * Author:      Jo Waltham
 * Author URI:  http://calliaweb.co.uk/
 * Text Domain: featured-custom-post-type-widget-for-genesis
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
*/

// if this file is called directly abort
if ( ! defined('WPINC' )) {
	die;
}
 
 // Register the widget
add_action( 'widgets_init', 'gfcptw_register_widget' );
function gfcptw_register_widget() {
	register_widget( 'Genesis_Featured_Custom_Post_Type');
}
 
require plugin_dir_path( __FILE__ ) . 'includes/class-featured-custom-post-type-widget-registrations.php';
