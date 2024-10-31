<?php
/* #? Check that this comes from admin panel */
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit('It Seems We Have a Security Issue!');
}

global $wpdb;
/* #? Go for DB entries */
if ( is_multisite() ) {
	if ( !empty( $_GET['networkwide'] ) ) {
		// Get blog list and cycle through all blogs
		$start_blog = $wpdb->blogid;
		$blog_list =$wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs );
		foreach ( $blog_list as $blog ) {
			switch_to_blog( $blog );
			// Call function to delete bug table with prefix
			erpUnnstl( $wpdb->get_blog_prefix() );
		}
		switch_to_blog( $start_blog );
		return;
	}
}

Unnstl( $wpdb->prefix );

function erpUnnstl( $prefix ) {
	global $wpdb;
	$wpdb->query( $wpdb->prepare( '
				DELETE FROM' . $prefix . $wpdb->postmeta . '
				WHERE meta_key = "pte_date"
				' ) );
}

/* That's all folks */
?>