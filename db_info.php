<?php
/*
Plugin Name: Nydalens SK - database
Plugin URI: http://nydalen.idrett.no
Description: Oppretter connection og funksjoner for custom php-widgets
Version: 1.1
Author: NSK Webadmin
Author URI: 
****************************
*/

include ('eventor/eventor_api_comm.php');

define('EVENTOR_ACTIVITY_CACHE_TTL', 60*1);

function open_nsk_connection() {
    
   // logindetaljer for mysql database
   $nskdb_host = "mysql.hosted.servetheworld.net";
   $nskdb_name = "nydalen_hoved";
   $nskdb_user = "nydalen_web";
   $nskdb_pwd  = "saftsuse";
   $nsk_connection = mysql_connect($nskdb_host, $nskdb_user, $nskdb_pwd) or print "<p>F?r ikke kontakt med databasen</p>";
   mysql_query("SET NAMES 'utf8'");
   //mysql_set_charset('utf8',$connection); 
   //@mysql_select_db( $nskdb_name );
   $nskdb_feilmelding = "<body><h1>Feil oppstod!</h1><p>Webserveren f?r ikke kontakt med databasen. Fors?k igjen senere. " .
                     "Vedvarer problemet, ta kontakt med <a href=\"mailto:pali@ingierd.com\">pali@ingierd.com</a></p></body></html>";
   $nsk_db = mysql_select_db($nskdb_name, $nsk_connection);
   return $nsk_connection;
}

function hent_nsk_paameldingsfrister() {
 
  $nsk_connection= open_nsk_connection();

  $query_string = "SELECT navn, pmldurl, " .
                      "date_format(fristpmelder, '%d.%m' ) AS formatfrist " .
                      "FROM nsk_paamelder " .
                      "WHERE start_visning <= now() and slutt_visning >= now() " .
                      "ORDER BY fristpmelder";

      $result = mysql_query($query_string, $nsk_connection);

      if ( mysql_num_rows( $result ) > 0 )
      {
         print "<ul>\n";

         while ( $row = mysql_fetch_array( $result ) )
         {
            print "<li><a href=\"" . $row['pmldurl'] . "\">" . $row['navn'] . "</a> (" . $row['formatfrist'] . ")</li>";
         }
         print getEventorActivities();
         print "</ul>\n";
      }
      else
      {
         print "Ingen frister &aring; vise n&aring;<br>";
      }
}

  function getEventorActivities() {
    $data = "";
	$fromDate=date("Y-m-d");
    $cache .= CACHE . "activity.cache";

    if (!file_exists($cache) || (file_exists($cache) && filemtime($cache) < (time() - EVENTOR_ACTIVITY_CACHE_TTL))) {

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
        
		$name = htmlentities($name);
		$date = new DateTime($registrationDeadline);
		$registrationDeadline = $date->format('d.m');

		$data .= "<li><a href=\"" . $url . "\">" . $name . "<a/> (" . $registrationDeadline . ")</li>";
      }

      $cachefile = fopen($cache, 'wb');
      fwrite($cachefile, $data);
      fclose($cachefile);
    } else {
      //echo "from cache (ttl: " . EVENTOR_ACTIVITY_CACHE_TTL . ", oldness " . (time()-filemtime($cache)) . ")";
      $data = file_get_contents($cache);
    }

    return $data;
  }

function hent_nsk_aktiviteter()
{
$connection= open_nsk_connection();
$query_string = "SELECT hva, link  " .
                      "FROM nsk_aktuelt " .
                      "WHERE dato >=now() " .
                      "ORDER BY dato";
      $result = mysql_query($query_string, $connection);

      if ( mysql_num_rows( $result ) > 0 )
      {
         print "<ul>\n";
         while ( $row = mysql_fetch_array( $result ) )
         {
            if ( $row['link'] )
            {
               print "<li><a href=\"" . $row['link'] . "\">" . $row['hva'] . "</a></li>";
            }
            else
            {
               print "<li>" .  $row['hva'] . "</li>";
            }            
         }
         print "</ul>\n";
      }
      else
      {
         print "Lite som skjer for tiden...";
      }
}
function hent_nsk_stifinnernytt()
{
	$connection= open_nsk_connection();

	$query_string = "SELECT tekst, tittel, date_format(dato, '%d.%m.%Y' ) AS fmtdato " .   
				"FROM nsk_stifinnernytt " .
				"WHERE " .
				"tittel IS NOT NULL " .
				"AND dato BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE() " .
				"ORDER BY dato DESC " .
				"LIMIT 3 ";

	$result = mysql_query($query_string, $connection);

	if ( mysql_num_rows( $result ) > 0 )
	{
		print "<ul>";
			while ($row = mysql_fetch_array($result)) {
				print "<li><a href=\"?s=sportslig/stifinnern/index.php\" title=\"".$row['fmtdato']."\">" . $row['tittel'] . "</a></li>";// (" . $row['fmtdato'] . ")";
			}
			print "</ul>";
	} else {
		print "Ingen nyheter siste måned..<br/>";
	}
}
?>
