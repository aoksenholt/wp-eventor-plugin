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

define('EVENTOR_API_KEY', "8ebc1e96796547518d68a8b37059e95e");
define('EVENTOR_API_BASE_URL', "https://eventor.orientering.no/api/");
define('EVENTOR_ORGANISATION_ID', 245); //
define('EVENTOR_ACTIVITY_CACHE_TTL', 60*1);

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

	// variables for the field and option names
	$opt_baseurl = 'mt_eventor_baseurl';
	$opt_apikey = 'mt_eventor_apikey';
	$hidden_field_name = 'mt_eventor_submit_hidden';
	$data_field_baseurl = 'mt_eventor_baseurl';
	$data_field_apikey = 'mt_evenor_apikey';

	// Read in existing option value from database
	$opt_baseurl_val = get_option( $opt_baseurl );
	$opt_apikey_val = get_option( $opt_apikey );

	// See if the user has posted us some information
	// If they did, this hidden field will be set to 'Y'
	if( $_POST[ $hidden_field_name ] == 'Y' ) {
		// Read their posted value
		$opt_baseurl_val = $_POST[ $data_field_baseurl ];
		$opt_apikey_val = $_POST[ $data_field_apikey ];

		// Save the posted value in the database
		update_option( $opt_baseurl, $opt_baseurl_val );
		update_option( $opt_apikey, $opt_apikey_val );

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
	name="<?php echo $data_field_baseurl; ?>"
	value="<?php echo $opt_baseurl_val; ?>" size="50"></p>
<hr />

<p><?php _e("API Key:", 'mt_trans_domain' ); ?> <input type="text"
	name="<?php echo $data_field_apikey; ?>"
	value="<?php echo $opt_apikey_val; ?>" size="50"></p>
<hr />

<p class="submit"><input type="submit" name="Submit"
	value="<?php _e('Update Options', 'mt_trans_domain' ) ?>" /></p>
<hr />

</form>
	<?php
}

function show_eventor($args) {

	extract($args);

	echo $before_widget.$before_title.$option_header.$after_title;

	echo $after_widget;
}

function init_eventor_widget() {
	register_sidebar_widget("Eventor Widget", "show_eventor");
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
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("ApiKey: " . EVENTOR_API_KEY));

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
	$fromDate=date("Y-m-d");
	$url = EVENTOR_API_BASE_URL . "activities?organisationId=" . EVENTOR_ORGANISATION_ID . "&from=" . $fromDate . "&to=2011-12-31&includeRegistrations=false";
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

		$name = htmlentities($name);//, ENT_QUOTES, 'UTF-8');
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

	if (!file_exists($cache) || (file_exists($cache) && filemtime($cache) < (time() - EVENTOR_ACTIVITY_CACHE_TTL))) {
		//echo "from eventor<br/>";

		$xml = getActivitiesFromEventor();
			
		//echo $xml;
			
		$data = makeActivitiesHtml($xml);

		$cachefile = fopen($cache, 'wb');
		fwrite($cachefile, $data);
		fclose($cachefile);
	} else {
		//echo "from cache (ttl: " . EVENTOR_ACTIVITY_CACHE_TTL . ", oldness " . (time()-filemtime($cache)) . ")<br/>";
		$data = file_get_contents($cache);
	}

	print $data;
}

//getActivities();
?>