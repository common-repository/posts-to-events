<?php
/*
Plugin Name:	Posts To Events
Plugin URI:		http://showcase.xdark.eu/poststoevents/
Description:	This is a simple plugin for adding callendar functionality to posts.
Version:		1.56
Author:			Panagiotis Vagenas 
Author URI:		http://xdark.eu
License:		GPLv3: see license.txt included in plugin folder.
*/

add_action('wp_enqueue_scripts','pte_stylesheet_loader');
/**
 * Load the plugins stylesheet
 *
 * @package WordPress
 * @since 1.0
 */
function pte_stylesheet_loader(){
	
	wp_register_style('ptestyle', plugins_url('pte_style.css',__FILE__));
	wp_enqueue_style('ptestyle');

}

/****************************************************************************/


/* #? If user is admin call the admin renderer */
if (is_admin()){
	require_once plugin_dir_path(__FILE__) . 'pte_admin.php';
} else {
	/* #? Load widget file */
	require_once plugin_dir_path(__FILE__) . 'widget.php';
}
/****************************************************************************/
?>