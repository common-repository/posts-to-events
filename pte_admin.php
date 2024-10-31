<?php
/* 
 * #? This file contains the admin panel options
 */
 
/* #? Load widget file */
require_once plugin_dir_path(__FILE__) . 'widget.php';


 /**
  * Loads jquerry scripts
  *
  * @package Wordpress
  * @since 1.0
  */
 function pte_jq_scripts() {
 	wp_enqueue_script( 'jquery' );
 	wp_enqueue_script( 'jquery-ui-core' );
 	wp_enqueue_script( 'jquery-ui-datepicker' );
 	wp_enqueue_style( 'datepickercss', plugins_url( 'inc/jquery-ui-1.10.2/css/excite-bike/jquery-ui-1.10.2.custom.css', __FILE__ ), array(), '1.10.2' );
 	wp_enqueue_style( 'wp-color-picker' );
 	wp_enqueue_script( 'wp-color-picker-script', plugins_url('script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
 }
add_action('admin_enqueue_scripts', 'pte_jq_scripts');


 /**
 * Display meta box
 *
 * @package Wordpress
 * @since 1.0
 * @param post class
 * @return void
 */
 function pte_date_metabox($post) {
 		
 	$datum	= get_post_meta($post->ID,'pte_date',false);
 	wp_nonce_field( plugin_basename(__FILE__), 'pte_date_nonce');
 	if (!empty($datum)) {
 		
 		echo '<table>';
 		foreach ($datum as $key){
 			echo '<tr><td><strong>' . date( "d M, Y", strtotime($key) ) . '</strong></td><td>' . '<button style="margin-left:10px;" id="' . $key . '" class="button Delete" type="submit" value="' . $key . '" name="pte_date_delete">Remove</button> '
 			.  '</td></tr>';
 		}
 		
 		echo  '
 			<tr style="display:block; border-top:1px solid silver;">
 				<td> <!--<button id="pte_date_but" class="button Submit" type="submit" title="text" value="' . '' . '" name="pte_date_delete">Add</button> -->
 				<script type="text/javascript">
 					jQuery( document ).ready( function() {
 						jQuery( "#pte_date" ).datepicker( { minDate: "+0", dateFormat: "yy-mm-dd",  constrainInput: true } );
 					} );
 				</script></td><td></td>
 		 	</tr> 
 		 	</table>' ;
 		 	echo '<input placeholder="Select date and hit update" type="date"  id="pte_date" name="pte_date"/>';
 	} 
 	else {
 		echo '
 			<p>
 				<input placeholder="Select date and hit update" type="date"  id="pte_date" name="pte_date"/>	
 				<script type="text/javascript">
 					jQuery( document ).ready( function() {
 						jQuery( "#pte_date" ).datepicker( {minDate: "+0", dateFormat: "yy-mm-dd", constrainInput: true} );
 					} );
 				</script></p>';
	}
 }
 
  /**
  * Add date selection meta box to post editor
  *
  * @package Wordpress
  * @since 1.0
 */
 function pte_register_metabox() {
 	global $post;
 	/* #? Register the date metabox for all post types*/
 	if(current_user_can( 'edit_post', $post->ID)){
 		$post_types = get_post_types(array(), 'objects');
 		foreach ($post_types as $pt){
 			add_meta_box('pte_datepicker_metabox', 'Posts To Events', 'pte_date_metabox', $pt->name, 'normal');
 		}
 	}
 }
 add_action('add_meta_boxes', 'pte_register_metabox');

 /**
  * Saves the date data 
  *
  * @package Wordpress
  * @since 1.0
  * @param post_id
  * @return void
  */
 function pte_date_save_data($post_id = FALSE) {

	/* #? Store date data in post meta table */
	if (!empty($_POST['pte_date']) && $_POST["pte_date"] === date('Y-m-d',strtotime($_POST["pte_date"])) && empty($_POST['pte_date_delete']) && wp_verify_nonce( $_POST['pte_date_nonce'],	plugin_basename(__FILE__) )) {
		if ( wp_is_post_revision( $post_id ) ) {add_post_meta($post_id, 'pte_date', $_POST['pte_date']);}
	}
		
 }
 add_action( 'save_post', 'pte_date_save_data' );
 
 /**
  * Deletes the date data
  *
  * @package Wordpress
  * @since 1.0
  * @param post_id
  * @return void
  */
 function pte_date_del_data($post_id = FALSE,$id) {
 	

 	/* #? Delete date data in post meta table */
 	if (!empty($_POST['pte_date_delete']) && $_POST["pte_date_delete"] === date('Y-m-d',strtotime($_POST["pte_date_delete"])) && wp_verify_nonce( $_POST['pte_date_nonce'],	plugin_basename(__FILE__) )) {
 			delete_post_meta($post_id, 'pte_date', $_POST["pte_date_delete"]);
 	}
 }
 add_action( 'save_post', 'pte_date_del_data',10,2 );
 
 ?>