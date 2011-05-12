<?php
/*
 Plugin Name: EventorPlugin
 Plugin URI: http://nydalen.idrett.no/eventorplugin
 Description: Plugin for fetching data from Eventor
 Version: 0.0.1
 Author: nsk
 Author URI: http://nydalen.idrett.no
 */

// Check out Eventor API documentation on https://eventor.orientering.se/api/documentation

define('MT_EVENTOR_BASEURL', 'mt_eventor_baseurl');
define('MT_EVENTOR_APIKEY', 'mt_eventor_apikey');
define('MT_EVENTOR_ORGID', 'mt_eventor_orgid');
define('MT_EVENTOR_ACTIVITY_TTL', 'mt_eventor_activity_ttl');
define('MT_EVENTOR_EVENTIDS', 'mt_eventor_eventids');


# Caching
define('CACHE', dirname(__FILE__) . '/cache/');

add_action('widgets_init', 'add_widget');

// Hook for adding admin menus
add_action('admin_menu', 'eventor_add_pages');

function add_widget()
{
	require_once 'EventorQueryWidget.php';
	register_widget('EventorQueryWidget');
}

function endsWith( $str, $sub )
{
	return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
}

// Automatic include of Query classes
function __autoload($class_name)
{
	if (endsWith($class_name, 'Query'))
	include 'Queries/' .$class_name . '.php';
}

/**
 * get current users role
 */
function get_user_role() {
	global $current_user;

	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);

	return $user_role;
}

/*
 * action function for menu hook
 */
function eventor_add_pages()
{
	add_options_page('Eventor', 'Eventor', 'administrator', 'eventor_options' , 'eventor_options_page');

	// add eventor deadlines tool if user is allowed to manage deadlines
	if (current_user_can('edit_eventor_event_ids'))
	{
		add_management_page('Eventor deadlines', 'Eventor deadlines', get_user_role(), 'eventor_management' , 'eventor_management_page');
	}
}

function eventor_management_page()
{
	$hidden_field_name = 'mt_eventor_submit_hidden';

	$opt_eventids_val = get_option( MT_EVENTOR_EVENTIDS );

	if( $_POST[ $hidden_field_name ] == 'Y' )
	{
		$opt_eventids_val = $_POST[ MT_EVENTOR_EVENTIDS ];

		update_option( MT_EVENTOR_EVENTIDS, $opt_eventids_val );
		?>
<div class="updated">
<p><strong><?php _e('Eventor deadlines saved.', 'mt_trans_domain' ); ?></strong></p>
</div>

		<?php
	}
	?>

	<?php
	echo '<div class="wrap">';

	echo "<h2>" . __( 'Eventor deadlines', 'mt_trans_domain' ) . "</h2>";
	?>
<form name="form1" method="post" action=""><input type="hidden"
	name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("EventorIds to show:", 'mt_trans_domain' ); ?> <input
	type="text" name="<?php echo MT_EVENTOR_EVENTIDS; ?>"
	value="<?php echo $opt_eventids_val; ?>" size="50"> <em>Commas
separeted list of Eventor Event IDs to show in deadlines widget</em></p>
<hr />

<p class="submit"><input type="submit" name="Submit"
	value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" /></p>
<hr />
</form>

	<?php
}

function eventor_options_page()
{
	$hidden_field_name = 'mt_eventor_submit_hidden';

	// Read in existing option value from database
	$opt_baseurl_val = get_option( MT_EVENTOR_BASEURL );
	$opt_apikey_val = get_option( MT_EVENTOR_APIKEY );
	$opt_orgid_val = get_option( MT_EVENTOR_ORGID );
	$opt_act_ttl_val = get_option( MT_EVENTOR_ACTIVITY_TTL );
	$opt_eventids_val = get_option( MT_EVENTOR_EVENTIDS );

	// See if the user has posted us some information
	// If they did, this hidden field will be set to 'Y'
	if( $_POST[ $hidden_field_name ] == 'Y' ) {
		// Read their posted value
		$opt_baseurl_val = $_POST[ MT_EVENTOR_BASEURL ];
		$opt_apikey_val = $_POST[ MT_EVENTOR_APIKEY ];
		$opt_orgid_val = $_POST[ MT_EVENTOR_ORGID ];
		$opt_act_ttl_val = $_POST[ MT_EVENTOR_ACTIVITY_TTL ];
		$opt_eventids_val = $_POST[ MT_EVENTOR_EVENTIDS ];

		// Save the posted value in the database
		update_option( MT_EVENTOR_BASEURL, $opt_baseurl_val );
		update_option( MT_EVENTOR_APIKEY, $opt_apikey_val );
		update_option( MT_EVENTOR_ORGID, $opt_orgid_val);
		update_option( MT_EVENTOR_ACTIVITY_TTL, $opt_act_ttl_val );
		update_option( MT_EVENTOR_EVENTIDS, $opt_eventids_val );

		// Put an options updated message on the screen

		?>
<div class="updated">
<p><strong><?php _e('Eventor settings saved.', 'mt_trans_domain' ); ?></strong></p>
</div>
		<?php

	}

	// Now display the options editing screen

	echo '<div class="wrap">';

	// header

	echo "<h2>" . __( 'Eventor Plugin Options', 'mt_trans_domain' ) . "</h2>";
	?>

<form name="form1" method="post" action=""><input type="hidden"
	name="<?php echo $hidden_field_name; ?>" value="Y">

<p><?php _e("Base URL:", 'mt_trans_domain' ); ?> <input type="text"
	name="<?php echo MT_EVENTOR_BASEURL; ?>"
	value="<?php echo $opt_baseurl_val; ?>" size="50"></p>

<p><?php _e("API Key:", 'mt_trans_domain' ); ?> <input type="text"
	name="<?php echo MT_EVENTOR_APIKEY; ?>"
	value="<?php echo $opt_apikey_val; ?>" size="50"></p>

<p><?php _e("Organisation ID:", 'mt_trans_domain' ); ?> <input
	type="text" name="<?php echo MT_EVENTOR_ORGID; ?>"
	value="<?php echo $opt_orgid_val; ?>" size="50"></p>

<p><?php _e("Club activities TTL:", 'mt_trans_domain' ); ?> <input
	type="text" name="<?php echo MT_EVENTOR_ACTIVITY_TTL; ?>"
	value="<?php echo $opt_act_ttl_val; ?>" size="50"></p>
<hr />

<p><?php _e("Widget List EventIds:", 'mt_trans_domain' ); ?> <input
	type="text" name="<?php echo MT_EVENTOR_EVENTIDS; ?>"
	value="<?php echo $opt_eventids_val; ?>" size="50"></p>
<hr />

<p class="submit"><input type="submit" name="Submit"
	value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" /></p>
<hr />

</form>
	<?php
}
?>