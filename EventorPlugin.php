<?php
/*
 Plugin Name: EventorPlugin
 Plugin URI: http://www.example.com/myplugin
 Description: This is a really great plugin that extends WordPress.
 Version: 1.0.0
 Author: nsk
 Author URI: http://nydalen.idrett.no
 */


/**
 * Capitalizes the title given by $title.
 */
function capitalizeTitle($title) {
	return strtoupper($title);
}

/**
 * Adds a custom field that prompts the user for their favorite
 * color.
 * @return void
 */
function drawCustomField() {
	echo '<p><label>Favorite Color:<br />';
	echo '<input autocomplete="off" class="input" name="fav_color" ';
	echo ' id="fav_color" size="25"';
	echo ' value="' . $_POST['fav_color'] . '" type="text" tabindex="32" />';
	echo '</label><br /></p>';
}

/* now add the filter */
add_filter('the_title', 'capitalizeTitle');

/* now add the action */
add_action('register_form', 'drawCustomField');

?>