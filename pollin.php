<?php
/*
Plugin Name: Pollin
Plugin URI: http://www.bin-co.com/tools/wordpress/plugins/pollin/
Description: Pollin wordpress plugin will let you create and add polls to your blog. It can be shown to your visitors who will be able to vote in the poll.
Version: 1.01.1
Author: Binny V A
Author URI: http://www.binnyva.com/
*/

/**
 * Add a new menu under Manage to manage Polls
 */
add_action( 'admin_menu', 'pollin_add_menu_links' );
function pollin_add_menu_links() {
	global $wp_version, $_registered_pages;
	$view_level= 'administrator';
	$page = 'edit.php';
	if($wp_version >= '2.7') $page = 'tools.php';
	
	add_submenu_page($page, __('Manage Polls', 'pollin'), __('Manage Polls', 'pollin'), $view_level, 'pollin/question.php' );

 	$code_pages = array('poll_result.php','question_form.php');
	foreach($code_pages as $code_page) {
		$hookname = get_plugin_page_hookname("pollin/$code_page", '' );
		$_registered_pages[$hookname] = true;
	}
}

/**
 * This will scan all the content pages that wordpress outputs for our special code. If the code is found, it will replace the requested quiz.
 */
add_shortcode( 'POLLIN', 'pollin_shortcode' );
function pollin_shortcode( $attr ) {
	$question_id = $attr[0];
	$contents = '';
	if(is_numeric($question_id)) { // Basic validiation - more on the file.
		ob_start();
		include(ABSPATH . 'wp-content/plugins/pollin/show_poll.php');
		$contents = ob_get_contents();
		ob_end_clean();
	}
	return $contents;
}

/**
 * This function can be called from the template
 */
function pollin_insert_poll($question_id = 0) {
	global $wpdb;
	if(!$question_id) $question_id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}pollin_question WHERE status=1 ORDER BY added_on DESC LIMIT 0,1");
	
	if(is_numeric($question_id)) { // Basic validiation - more on the file.
		ob_start();
		include(ABSPATH . 'wp-content/plugins/pollin/show_poll.php');
		$contents = ob_get_contents();
		ob_end_clean();

		print $contents;
	}
}

function pollin_show_widget() {
	$id = get_option('pollin_widget_poll_id');
	
	print "<li><h3>". get_option('pollin_widget_title') . "</h3>";
	pollin_insert_poll($id);
	print "</li>";
}

/// Pollin as a Sidebar widget
function pollin_widget_init() {
	if (! function_exists("register_sidebar_widget")) return;
	
	function pollin_show_options() {
		if ( $_POST['pollin-submit'] ) {
			update_option('pollin_widget_title', $_REQUEST['pollin_widget_title']);
			update_option('pollin_widget_poll_id', $_REQUEST['pollin_widget_poll_id']);
		}
		echo '<p style="text-align:right;"><label for="pollin_widget_title">'.t('Title').': <input style="width: 200px;" id="pollin_widget_title" name="pollin_widget_title" type="text" value="'.get_option("pollin_widget_title").'" /></label></p>';
		echo '<p style="text-align:right;"><label for="pollin_widget_poll_id">'.t('Poll ID').': <input style="width: 200px;" id="pollin_widget_poll_id" name="pollin_widget_poll_id" type="text" value="'.get_option("pollin_widget_poll_id").'" /></label></p>';
		echo '<input type="hidden" id="pollin-submit" name="pollin-submit" value="1" />';
	}
	
	register_sidebar_widget('Poll', 'pollin_show_widget');
	register_widget_control('Poll', 'pollin_show_options', 200, 100);
}
add_action('plugins_loaded', 'pollin_widget_init');


/**
 * Stuff to do when the plugin is activated - create the tables, stuff like that.
 */
add_action('activate_pollin/pollin.php','pollin_activate');
function pollin_activate() {
	global $wpdb;
	
	$database_version = '2';
	$installed_db = get_option('pollin_db_version');
	
	if($database_version != $installed_db) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pollin_answer (
				ID int(11) unsigned NOT NULL auto_increment,
				question_ID int(11) unsigned NOT NULL,
				answer varchar(255) default NULL,
				sort_order int(3) NOT NULL,
				votes int(5) NOT NULL default '0',
				PRIMARY KEY  (ID),
				KEY question_ID (question_ID)
			);
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}pollin_question (
				ID int(11) unsigned NOT NULL auto_increment,
				question mediumtext NOT NULL,
				added_on datetime NOT NULL,
				status enum('1','0') NOT NULL default '1',
				PRIMARY KEY  (ID)
			);";
		dbDelta($sql);
	}
}


/**
 * Internal function
 */
function pollin_nextColor() {
	global $colors, $color_index;
	
	$color_index++;
	if($color_index > count($colors)) $color_index = 0;
	
	return "#" . $colors[$color_index - 1];
}
