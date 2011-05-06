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

define(MT_EVENTOR_BASEURL, 'mt_eventor_baseurl');
define(MT_EVENTOR_APIKEY, 'mt_eventor_apikey');
define(MT_EVENTOR_ORGID, 'mt_eventor_orgid');
define(MT_EVENTOR_ACTIVITY_TTL, 'mt_eventor_activity_ttl');

# Caching
define(CACHE, dirname(__FILE__) . '/cache/');

add_action('widgets_init', 'add_widget');

// Hook for adding admin menus
add_action('admin_menu', 'eventor_add_pages');

add_action("plugins_loaded", "init_eventor_widget");

function add_widget()
{
	require_once 'EventorWidget.php';
	register_widget('Eventor_Widget_ClubDeadlines');
}


// action function for above hook
function eventor_add_pages() {
	add_options_page('Eventor', 'Eventor', 'administrator', 'eventor', 'eventor_options_page');
}
// donate_options_page() displays the page content for the Test Options submenu
function eventor_options_page() {

	$hidden_field_name = 'mt_eventor_submit_hidden';

	// Read in existing option value from database
	$opt_baseurl_val = get_option( MT_EVENTOR_BASEURL );
	$opt_apikey_val = get_option( MT_EVENTOR_APIKEY );
	$opt_orgid_val = get_option( MT_EVENTOR_ORGID );
	$opt_act_ttl_val = get_option( MT_EVENTOR_ACTIVITY_TTL );

	// See if the user has posted us some information
	// If they did, this hidden field will be set to 'Y'
	if( $_POST[ $hidden_field_name ] == 'Y' ) {
		// Read their posted value
		$opt_baseurl_val = $_POST[ MT_EVENTOR_BASEURL ];
		$opt_apikey_val = $_POST[ MT_EVENTOR_APIKEY ];
		$opt_orgid_val = $_POST[ MT_EVENTOR_ORGID ];
		$opt_act_ttl_val = $_POST[ MT_EVENTOR_ACTIVITY_TTL ];

		// Save the posted value in the database
		update_option( MT_EVENTOR_BASEURL, $opt_baseurl_val );
		update_option( MT_EVENTOR_APIKEY, $opt_apikey_val );
		update_option( MT_EVENTOR_ORGID, $opt_orgid_val);
		update_option( MT_EVENTOR_ACTIVITY_TTL, $opt_act_ttl_val );

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
<hr />

<p><?php _e("API Key:", 'mt_trans_domain' ); ?> <input type="text"
	name="<?php echo MT_EVENTOR_APIKEY; ?>"
	value="<?php echo $opt_apikey_val; ?>" size="50"></p>
<hr />

<p><?php _e("Organisation ID:", 'mt_trans_domain' ); ?> <input
	type="text" name="<?php echo MT_EVENTOR_ORGID; ?>"
	value="<?php echo $opt_orgid_val; ?>" size="50"></p>
<hr />

<p><?php _e("Club activities TTL:", 'mt_trans_domain' ); ?> <input
	type="text" name="<?php echo MT_EVENTOR_ACTIVITY_TTL; ?>"
	value="<?php echo $opt_act_ttl_val; ?>" size="50"></p>
<hr />

<p class="submit"><input type="submit" name="Submit"
	value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" /></p>
<hr />

</form>
	<?php
}

function eventorApiCall($url)
{
	// create curl resource
	$ch = curl_init();
	// set url
	curl_setopt($ch, CURLOPT_URL, $url);
	// return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	// set header
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("ApiKey: " . get_option(MT_EVENTOR_APIKEY)));

	// $output contains the output string
	$output = curl_exec($ch);

	if (!$output)
	echo curl_error($ch);

	// close curl resource to free up system resources
	curl_close($ch);

	return $output;
}

function getActivitiesFromEventor()
{
	$fromDate = date("Y-m-d");
	$toDate = date("Y-m-d", strtotime("+1 year", strtotime($fromDate)));
		
	$url = get_option(MT_EVENTOR_BASEURL) . "activities?organisationId=" . get_option(MT_EVENTOR_ORGID) . "&from=" . $fromDate . "&to=" . $toDate . "&includeRegistrations=false";
	$xml = eventorApiCall($url);

	return $xml;
}

function makeActivitiesHtml($xml)
{
	$activities = array();

	$doc = simplexml_load_string($xml);
	$activityNodes = $doc->Activity;

	$data = '<ul>';

	foreach ($doc->Activity as $activity) {
		$name = $activity->Name;
		$url = $activity['url'];
		$numRegistrations = $activity['registrationCount'];
		$registrationDeadline = $activity['registrationDeadline'];

		$name = htmlentities($name);
		$date = new DateTime($registrationDeadline);
		$registrationDeadline = $date->format('j/n H:i');

		$data .= "<li><a href=\"" . $url . "\">" . $name . "</a> (" . $numRegistrations . ") - " . $registrationDeadline . "</li>";
	}

	$data .= '</ul>';

	return $data;
}


function getActivities() {
	$data = "";

	$cache .= CACHE . "activity.cache";

	if (!file_exists($cache) || (file_exists($cache) && filemtime($cache) < (time() - get_option(MT_EVENTOR_ACTIVITY_TTL)))) {
		//echo "from eventor<br/>";

		$xml = getActivitiesFromEventor();
			
		$data = makeActivitiesHtml($xml);

		$cachefile = fopen($cache, 'wb');
		fwrite($cachefile, $data);
		fclose($cachefile);
	} else {
		//echo "from cache (ttl: " . get_option(MT_EVENTOR_ACTIVITY_TTL) . ", oldness " . (time()-filemtime($cache)) . ")<br/>";
		$data = file_get_contents($cache);
	}

	print $data;
}
?>