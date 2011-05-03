<?php
/*
 Plugin Name: EventorPlugin
 Plugin URI: http://nydalen.idrett.no/eventorplugin
 Description: Plugin for fetching data from Eventor
 Version: 0.0.1
 Author: nsk
 Author URI: http://nydalen.idrett.no
 */

  define('EVENTOR_API_KEY', "8ebc1e96796547518d68a8b37059e95e");
  define('EVENTOR_API_BASE_URL', "https://eventor.orientering.no/api/");
  define('EVENTOR_ORGANISATION_ID', 245); //
  define('EVENTOR_ACTIVITY_CACHE_TTL', 60*1);

  # Caching
  define(CACHE, dirname(__FILE__) . '/cache/');

  function eventorApiCall($url)
  {
    // create curl resource
    $ch = curl_init();
    // set url
    curl_setopt($ch, CURLOPT_URL, $url);
    // return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // set header
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("ApiKey: " . EVENTOR_API_KEY));

    // $output contains the output string
    $output = curl_exec($ch);

    // close curl resource to free up system resources
    curl_close($ch);

//echo $output;

    return $output;
  }

  function getActivities() {
    $data = "";
	$fromDate=date("Y-m-d");
    $cache .= CACHE . "activity.cache";

    if (!file_exists($cache) || (file_exists($cache) && filemtime($cache) < (time() - EVENTOR_ACTIVITY_CACHE_TTL))) {
echo "from eventor<br/>";

      $url = EVENTOR_API_BASE_URL . "activities?organisationId=" . EVENTOR_ORGANISATION_ID . "&from=" . $fromDate . "&to=2011-12-31&includeRegistrations=false";
      $xml = eventorApiCall($url);

      $activities = array();

      $doc = simplexml_load_string($xml);
      $activityNodes = $doc->Activity;

      foreach ($doc->Activity as $activity) {
        $name = $activity->Name;
        $url = $activity['url'];
        $numRegistrations = $activity['registrationCount'];
        $registrationDeadline = $activity['registrationDeadline'];
		$name = htmlentities($name);//, ENT_QUOTES, 'UTF-8');
		
        $data .= "<a href=\"" . $url . "\">" . $name . "<a/> (" . $numRegistrations . ") - " . $registrationDeadline . "<br/>";
      }

      $cachefile = fopen($cache, 'wb');
      fwrite($cachefile, $data);
      fclose($cachefile);
    } else {
echo "from cache (ttl: " . EVENTOR_ACTIVITY_CACHE_TTL . ", oldness " . (time()-filemtime($cache)) . ")<br/>";
      $data = file_get_contents($cache);
    }

    print $data;
  }

  getActivities();
?>