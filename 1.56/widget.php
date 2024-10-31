<?php
/**
 * Adds Post_Events_Widget widget.
 */
class Post_Events_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct('Post_Events_widget', 'Post To Events',array( 'description' => __( 'Add some posts as events' ) ), array( 'width' => 350 ) );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		// outputs the content of the widget
		
		extract( $args );
		// Retrieve widget configuration options
		$event_count = ( !empty( $instance['pte_opt_eve_to_show'] ) ? $instance['pte_opt_eve_to_show'] : 10 );
		$pte_opt_eve_show_after = ( !empty( $instance['pte_opt_eve_show_after'] ) ? $instance['pte_opt_eve_show_after'] : 0 );
		$widget_title = ( !empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : 'Upcoming Events' );
		$titlew = apply_filters( 'widget_title', $widget_title );
		$pte_exc_or_tit = (!empty($instance['pte_exc_or_tit'])) ? ($instance['pte_exc_or_tit']) : 'post_title';
		$thumb_dis = (!empty($instance['pte_thumb'])) ? ($instance['pte_thumb']) : 0;
		$pte_gen_o = (!empty($instance['pte_gen'])) ? ($instance['pte_gen']) : 0;
		$pte_dt_s = (!empty( $instance['pte_opt_dt_s'] ) ? $instance['pte_opt_dt_s'] : '' );
		$pte_con_s = (!empty( $instance['pte_opt_con_s'] ) ? $instance['pte_opt_con_s'] : '' );
		$pte_dt_c = (!empty( $instance['pte_opt_dt_c'] ) ? $instance['pte_opt_dt_c'] : '' );
		$pte_con_c = (!empty( $instance['pte_opt_con_c'] ) ? $instance['pte_opt_con_c'] : '' );
		$pte_dt_c_u = (!empty( $instance['pte_opt_dt_c_u'] ) ? $instance['pte_opt_dt_c_u'] : '' );
		$pte_con_c_u = (!empty( $instance['pte_opt_con_c_u'] ) ? $instance['pte_opt_con_c_u'] : '' );
		$pte_thumb_crop = (!empty( $instance['pte_thumb_crop'] ) ? $instance['pte_thumb_crop'] : '' );
		$pte_thumb_h = (!empty( $instance['pte_thumb_h'] ) ? $instance['pte_thumb_h'] : '' );
		$pte_dt_format = (!empty( $instance['pte_opt_dt_form'] ) ? $instance['pte_opt_dt_form'] : '' );		
		$date_color = $pte_dt_c_u ? $pte_dt_c : "";
		$con_color = $pte_con_c_u ? $pte_con_c : "";
		
		$afterDate = date('Y-m-d', strtotime(date("Y-m-d") . (-$pte_opt_eve_show_after+1) . 'days'));
		global $post; /* #! Unsetted bellow */
		$argu = array(
			'post_status'			=> 'publish',
			'post_visibility'		=> 'public',
			'post__not_in'			=> (array) $post->ID,
		    'meta_key'				=> 'pte_date',
		    'orderby'				=> 'meta_value',
		    'order'					=> 'ASC',
		    'posts_per_page'        => $event_count,
			'ignore_sticky_posts' 	=> 1,
			'meta_query'			=> array(
 		       array(
 		           'key' 		=> 'pte_date',
 		           'value' 		=> $afterDate,
 		           'type' 		=> 'date',
 		           'compare' 	=> '>='
 		       )
  		  ) 
		);
		unset($post);
		$the_query = new WP_Query( $argu );
		
		if (!$pte_gen_o || $the_query->have_posts()) {
			
			echo $before_widget;
			
			echo $before_title . $titlew . $after_title;

			if ( $the_query->have_posts() ) {
				
				// Display posts in unordered list layout
				echo '<div id="pte_container">';
				echo '<ul class="pte_ul_elem">';
				// Cycle through all items retrieved
				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					
					/* TODO At this point we check if the post spans on multiple dates. This results in an array but the chronological order isn't right 
					 * We should store all (right now we check for each post) events in an array and then put them in order
					 */
					$countDates = get_post_meta(get_the_ID(),'pte_date',false);
					sort($countDates);
					foreach ($countDates as $dcbdates ){
						if ( $dcbdates > $afterDate ) {
								
							$content = $pte_exc_or_tit == 'post_excerpt' ? '<span class="pte_content" style="font-size:' . ($pte_con_s ? $pte_con_s . "px" : "100%") /* TODO DEBUG THIS */ . '; color:' . $con_color . '">' . wp_trim_excerpt() /* TODO DEBUG THIS */ . '</span>' : '<span class="pte_content"  style="font-size:' . ($pte_con_s ? $pte_con_s . "px" : "100%") . '; color:' . $con_color . '">' . get_the_title( get_the_ID() ) . '</span>';
								
							/* #? Get the thumb, medium size should be ok for most of themes */
							if ($pte_thumb_crop && $pte_thumb_h && has_post_thumbnail(get_the_ID())) {
								/* Fixed croped images not loading */
								$th = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()),"medium");
								$src = substr($th[0], strlen(get_bloginfo('wpurl')));
								$th = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()),"medium");
								$thumb_display = '<img src="' . plugins_url( basename( dirname( __FILE__ ) ) ) . '/inc/tim/timthumb.php?src=' . $src . '&h=' . ($pte_thumb_h>$th[2] ? $th[2] : $pte_thumb_h) . '&w=' . 200 /* TODO Maybe we should change this */ . '&q=90" />';
							} else if (has_post_thumbnail(get_the_ID())){
								$thumb_display = $thumb_dis ? get_the_post_thumbnail(get_the_ID(), 'medium' ) : '';
							} else {
								$thumb_display = '';
							}

							echo '<li class="pte_li_elem" ><a class="pte_a_elem"  href="' . get_permalink() . '">';
							echo '<span class="pte_date_elem" style="font-size:' . ($pte_dt_s ? $pte_dt_s . "px" : "100%" ) . '; color:' . $date_color . '">' . date_i18n( $pte_dt_format, strtotime($dcbdates)) . '</span><br>';
							echo $thumb_display  . '<br>' . $content . '</a><hr></li>';
						}
					}
				}
				echo '</ul>';
				echo '</div>';
			}
		}
		wp_reset_query();


		echo $after_widget;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		// outputs the options form on admin
		// processes widget options to be saved
		/* #? Title of the widget */
		$title = (isset($instance['title'])) ? ( $instance['title']) : ( __('Upcoming Events'));
		/* #? Events to show */
		$pte_opt_eve_to_show = (isset($instance['pte_opt_eve_to_show'])) ? ( $instance['pte_opt_eve_to_show']) : 10;
		/* #? Hide event after x days */
		$pte_opt_eve_show_after = (isset($instance['pte_opt_eve_show_after'])) ? ( $instance['pte_opt_eve_show_after']) : 0;
		/* #? Show excerpt or title */
		$pte_exc_or_tit = (isset($instance['pte_exc_or_tit'])) ? ( $instance['pte_exc_or_tit']) : 'post_title';
		/* #? Show post thumbnail */
		$pte_thumb = (isset($instance['pte_thumb'])) ? ( $instance['pte_thumb']) : 1;
		/* #? Hide if no events */
		$pte_gen = (isset($instance['pte_gen'])) ? ($instance['pte_gen']) : 0;
		/* #? Date text size */
		$pte_opt_dt_s = (isset($instance['pte_opt_dt_s'])) ? ($instance['pte_opt_dt_s']) : 0;
		/* #? Content text size */
		$pte_opt_con_s = (isset($instance['pte_opt_con_s'])) ? ($instance['pte_opt_con_s']) : 0;
		/* #? Date text color */
		$pte_opt_dt_c = (isset($instance['pte_opt_dt_c'])) ? ($instance['pte_opt_dt_c']) : '#ffffff';
		/* #? Content text color */
		$pte_opt_con_c = (isset($instance['pte_opt_con_c'])) ? ($instance['pte_opt_con_c']) : '#ffffff';
		/* #? Date text color use*/
		$pte_opt_dt_c_u = (isset($instance['pte_opt_dt_c_u'])) ? ($instance['pte_opt_dt_c_u']) : 0;
		/* #? Content text color use*/
		$pte_opt_con_c_u = (isset($instance['pte_opt_con_c_u'])) ? ($instance['pte_opt_con_c_u']) : 0;
		/* #? Crop thumbnail*/
		$pte_thumb_crop = (isset($instance['pte_thumb_crop'])) ? ($instance['pte_thumb_crop']) : 0;
		/* #? Croped thumbnail height */
		$pte_thumb_h = (isset($instance['pte_thumb_h'])) ? ( $instance['pte_thumb_h']) : 0;
		/* #? Date formater */
		$pte_opt_dt_form = (isset($instance['pte_opt_dt_form'])) ? ( $instance['pte_opt_dt_form']) : 'd M,Y';
		?>
		<?php 	/* #? Create nonce */ ?>
		<input type="hidden" name="my_meta_box_nonce" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="pte_wid_opt1" size="39pt" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'pte_opt_eve_to_show' ); ?>"><?php _e( 'Events to show:' ); ?></label>
			<input class="pte_wid_opt2" size="3pt" id="<?php echo $this->get_field_id( 'pte_opt_eve_to_show' ); ?>" name="<?php echo $this->get_field_name( 'pte_opt_eve_to_show' ); ?>" type="number" value="<?php echo esc_attr( $pte_opt_eve_to_show ); ?>" /> | 
		
			<label for="<?php echo $this->get_field_id( 'pte_gen' ); ?>"><?php _e( 'Hide if no events to show:' ); ?></label>
			<input class="pte_wid_opt5" id="<?php echo $this->get_field_id( 'pte_gen' ); ?>" name="<?php echo $this->get_field_name( 'pte_gen' ); ?>" type="checkbox" <?php echo checked( $pte_gen ); ?> /><br>
			
			<label for="<?php echo $this->get_field_id( 'pte_opt_eve_show_after' ); ?>"><?php _e( 'Hide event after ' ); ?></label>
			<input class="pte_wid_opt2" size="2pt" id="<?php echo $this->get_field_id( 'pte_opt_eve_show_after' ); ?>" name="<?php echo $this->get_field_name( 'pte_opt_eve_show_after' ); ?>" type="number" value="<?php echo esc_attr( $pte_opt_eve_show_after ); ?>" /> days
		</p>
		<hr>
		<p style="text-align: center">
		<strong>Thumbnail</strong></p>
		<p>
			<label for="<?php echo $this->get_field_id( 'pte_thumb' ); ?>"><?php _e( 'Show post thumbnail:' ); ?></label>
			<input class="pte_wid_opt4" id="<?php echo $this->get_field_id( 'pte_thumb' ); ?>" name="<?php echo $this->get_field_name( 'pte_thumb' ); ?>" type="checkbox" <?php echo checked( $pte_thumb ); ?> />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'pte_thumb_crop' ); ?>"><?php _e( 'Crop thumbnail:' ); ?></label>
			<input class="pte_wid_opt4" id="<?php echo $this->get_field_id( 'pte_thumb_crop' ); ?>" name="<?php echo $this->get_field_name( 'pte_thumb_crop' ); ?>" type="checkbox" <?php echo checked( $pte_thumb_crop ); ?> /> : 
			<label style="margin-left:25%;" for="<?php echo $this->get_field_id( 'pte_thumb_h' ); ?>"><?php _e( 'Set height:' ); ?></label>
			<input class="pte_wid_opt2" size="3pt" id="<?php echo $this->get_field_id( 'pte_thumb_h' ); ?>" name="<?php echo $this->get_field_name( 'pte_thumb_h' ); ?>" type="number" value="<?php echo esc_attr( $pte_thumb_h ); ?>" />
		</p>
		<hr>
		<p>
			<label for="<?php echo $this->get_field_id( 'pte_exc_or_tit' ); ?>"><?php echo 'Display post: '; ?> 
				<select class="pte_wid_opt3" id="<?php echo $this->get_field_id ( 'pte_exc_or_tit' ); ?>" name="<?php echo $this->get_field_name ( 'pte_exc_or_tit' ); ?>">
					<option value="post_title" <?php selected( $pte_exc_or_tit, 'post_title' ); ?>>Title</option>
					<option value="post_excerpt" <?php selected( $pte_exc_or_tit, 'post_excerpt' ); ?>>Excerpt</option>
				</select>
			</label><br>
			<label for="<?php echo $this->get_field_id( 'pte_opt_dt_form' ); ?>"><?php echo 'Date format: '; ?> 
				<select class="pte_wid_opt3" id="<?php echo $this->get_field_id ( 'pte_opt_dt_form' ); ?>" name="<?php echo $this->get_field_name ( 'pte_opt_dt_form' ); ?>">
					<option value="D, d F" <?php selected( $pte_opt_dt_form, 'D, d F' ); ?>><?php echo date_i18n("D, d F",time())?></option>
					<option value="d M,Y" <?php selected( $pte_opt_dt_form, 'd M,Y' ); ?>><?php echo date_i18n("d M,Y",time())?></option>
					<option value="d M" <?php selected( $pte_opt_dt_form, 'd M' ); ?>><?php echo date_i18n("d M",time())?></option>
					<option value="m/d" <?php selected( $pte_opt_dt_form, 'm/d' ); ?>><?php echo date_i18n("m/d",time())?> (USA)</option>
					<option value="d/m" <?php selected( $pte_opt_dt_form, 'd/m' ); ?>><?php echo date_i18n("d/m",time())?> (Europe)</option>
					<option value="m/d/Y" <?php selected( $pte_opt_dt_form, 'm/d/Y' ); ?>><?php echo date_i18n("m/d/Y",time())?> (USA)</option>
					<option value="d/m/Y" <?php selected( $pte_opt_dt_form, 'd/m/Y' ); ?>><?php echo date_i18n("d/m/Y",time())?> (Europe)</option>
				</select>
			</label>
		</p>
		<p style="text-align: center">
		<strong>Text properties</strong></p>
		<table style="vertical-align:middle;">
			<tr>
    			<td>Date size: </td>
    			<td><label for="<?php echo $this->get_field_id( 'pte_opt_dt_s' ); ?>"></label>
				<input  size="1pt" id="<?php echo $this->get_field_id( 'pte_opt_dt_s' ); ?>" name="<?php echo $this->get_field_name( 'pte_opt_dt_s' ); ?>" type="number" value="<?php echo esc_attr( $pte_opt_dt_s ); ?>" />px <em style="font-size:9px;"> ('0' for theme default)</em></td>
  			</tr>
  			<tr>
    			<td>Date color: </td>
  				<td><label for="<?php echo $this->get_field_id( 'pte_opt_dt_c' ); ?>"></label>
				<input class="wp-color-picker-field" data-default-color="#ffffff" size="1pt" id="<?php echo $this->get_field_id( 'pte_opt_dt_c' ); ?>" name="<?php echo $this->get_field_name( 'pte_opt_dt_c' ); ?>" type="text" value="<?php echo esc_attr( $pte_opt_dt_c ); ?>" /></td>
				<td><label for="<?php echo $this->get_field_id( 'pte_opt_dt_c_u' ); ?>"><?php _e( 'Use:' ); ?></label>
				<input class="pte_wid_opt5" id="<?php echo $this->get_field_id( 'pte_opt_dt_c_u' ); ?>" name="<?php echo $this->get_field_name( 'pte_opt_dt_c_u' ); ?>" type="checkbox" <?php echo checked( $pte_opt_dt_c_u ); ?> /></td>
  			</tr>
  			<tr>
    			<td>Content size: </td>
  				<td><label for="<?php echo $this->get_field_id( 'pte_opt_con_s' ); ?>"></label>
				<input  size="1pt" id="<?php echo $this->get_field_id( 'pte_opt_con_s' ); ?>" name="<?php echo $this->get_field_name( 'pte_opt_con_s' ); ?>" type="number" value="<?php echo esc_attr( $pte_opt_con_s ); ?>" />px <em style="font-size:9px;"> ('0' for theme default)</em></td>
  			</tr>
  			<tr>
    			<td>Content color: </td>
  				<td><label for="<?php echo $this->get_field_id( 'pte_opt_con_c' ); ?>"></label>
				<input class="wp-color-picker-field" data-default-color="#ffffff" size="3pt" id="<?php echo $this->get_field_id( 'pte_opt_con_c' ); ?>" name="<?php echo $this->get_field_name( 'pte_opt_con_c' ); ?>" type="text" value="<?php echo esc_attr( $pte_opt_con_c ); ?>" /></td>
				<td><label for="<?php echo $this->get_field_id( 'pte_opt_con_c_u' ); ?>"><?php _e( 'Use:' ); ?></label>
				<input class="pte_wid_opt5" id="<?php echo $this->get_field_id( 'pte_opt_con_c_u' ); ?>" name="<?php echo $this->get_field_name( 'pte_opt_con_c_u' ); ?>" type="checkbox" <?php echo checked( $pte_opt_con_c_u ); ?> /></td>
  			</tr>
  		</table>
  		<script type="text/javascript">
  			jQuery(document).ready(function($){$('#<?php echo $this->get_field_id( "pte_opt_dt_c" ); ?>').wpColorPicker();});
			jQuery(document).ready(function($){$('#<?php echo $this->get_field_id( "pte_opt_con_c" ); ?>').wpColorPicker();});
  		</script>
		<?php 
	}
	
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		/* #? Verify nonce */
		if ( !isset($_POST['my_meta_box_nonce']) && !wp_verify_nonce( $_POST['my_meta_box_nonce'], 'my_meta_box_nonce' ) ){
			return ;
		}	
		$instance = array();
		
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['pte_opt_eve_to_show'] = (is_numeric($new_instance["pte_opt_eve_to_show"]) && $new_instance["pte_opt_eve_to_show"]>0) ? ($new_instance['pte_opt_eve_to_show'] ) : ($old_instance['pte_opt_eve_to_show'] );
		$instance['pte_opt_eve_show_after'] = is_numeric($new_instance["pte_opt_eve_show_after"]) ? ($new_instance['pte_opt_eve_show_after'] ) : ($old_instance['pte_opt_eve_show_after'] );
		$instance['pte_exc_or_tit'] = strip_tags( $new_instance['pte_exc_or_tit'] );
		$instance['pte_thumb'] = (bool) $new_instance["pte_thumb"] ? 1 : 0;
		$instance['pte_gen'] = (bool) $new_instance["pte_gen"] ? 1 : 0;
		$instance['pte_opt_dt_s'] = (is_numeric($new_instance["pte_opt_dt_s"])) ? ($new_instance['pte_opt_dt_s'] ) : ($old_instance['pte_opt_dt_s'] );
		$instance['pte_opt_con_s'] = (is_numeric($new_instance["pte_opt_con_s"])) ? ($new_instance['pte_opt_con_s'] ) : ($old_instance['pte_opt_con_s'] );
		$instance['pte_opt_dt_c'] = strip_tags($new_instance["pte_opt_dt_c"]);
		$instance['pte_opt_con_c'] = strip_tags($new_instance["pte_opt_con_c"]);
		$instance['pte_opt_dt_c_u'] = (bool) $new_instance["pte_opt_dt_c_u"] ? 1 : 0;
		$instance['pte_opt_con_c_u'] = (bool) $new_instance["pte_opt_con_c_u"] ? 1 : 0;
		$instance['pte_thumb_crop'] = (bool) $new_instance["pte_thumb_crop"] ? 1 : 0;
		$instance['pte_thumb_h'] = (is_numeric($new_instance["pte_thumb_h"]) && $new_instance["pte_thumb_h"]>0) ? ($new_instance['pte_thumb_h'] ) : ($old_instance['pte_thumb_h'] );
		$instance['pte_opt_dt_form'] =   wp_kses( $new_instance["pte_opt_dt_form"]  , array(' ', '/', ','));
				
		return $instance;
	}	
} // class Post_Events_Widget

// register Post_Events_Widget widget
add_action( 'widgets_init', create_function( '', 'register_widget( "Post_Events_widget" );' ) );
 