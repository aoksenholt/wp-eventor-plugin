<html>
<form method=post><?php
// usage: http://url/wp-content/plugins/EventorPlugin/EventorApiTest.php

// Stub for Wordpress add_action function since EventorPlugin.php hook up top Wordpress with it.
function add_action($a1, $a2){}

// Stub for Wordpress API
function get_option($option)
{
	switch ($option)
	{
		case MT_EVENTOR_APIKEY:
			return "8ebc1e96796547518d68a8b37059e95e";
		case MT_EVENTOR_BASEURL:
			return "https://eventor.orientering.no";
	}
}

// Require the real plugin code
require_once ('EventorPlugin.php');

class DebugQuery extends Query
{
	private $url;
	
	public function __construct($url)
	{
		$this->url = $url;	
	}
	
	function getQueryUrl()
	{
		return $this->url;
	}	
	
	function formatHtml($xml)
	{
		return '';
	}
	
}

function formatXmlString($xml)
{

	// add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
	$xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

	// now indent the tags
	$token      = strtok($xml, "\n");
	$result     = ''; // holds formatted version as it is built
	$pad        = 0; // initial indent
	$matches    = array(); // returns from preg_matches()

	// scan each line and adjust indent based on opening/closing tags
	while ($token !== false) :

	// test for the various tag states

	// 1. open and closing tags on same line - no change
	if (preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
	$indent=0;
	// 2. closing tag - outdent now
	elseif (preg_match('/^<\/\w/', $token, $matches)) :
	$pad--;
	// 3. opening tag - don't pad this one, only subsequent tags
	elseif (preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
	$indent=1;
	// 4. no indentation needed
	else :
	$indent = 0;
	endif;

	// pad the line with the required number of leading spaces
	$line    = str_pad($token, strlen($token)+$pad, ' ', STR_PAD_LEFT);
	$result .= $line . "\n"; // add to the cumulative result, with linefeed
	$token   = strtok("\n"); // get the next token
	$pad    += $indent; // update the pad size for subsequent lines
	endwhile;

	return $result;
}

function doEventorApiCall()
{
	$eventorApiUrl = '';
	
	if(isset($_POST['eventorApiUrl']))
	{
		$eventorApiUrl = $_POST['eventorApiUrl'];
	}	
?>
<p>Submit API query to inspect response. </p>

<p> 
<?php echo get_option(MT_EVENTOR_BASEURL);?>/api/<input size=200 type=text name=eventorApiUrl value='<?php echo $eventorApiUrl; ?>' /> <br />
<input type=submit name=test> 
</p>
<?php 
		
	if (empty($eventorApiUrl))
	{
		return;
	}

	$query = new DebugQuery($eventorApiUrl);

	$query->load();
	$xml = $query->getXml();	

	$xmlString = formatXmlString($xml);

	echo "Eventor Response<br /><textarea rows=30 cols=176>$xmlString</textarea>";
}
doEventorApiCall();
?>
<p>
<a href=https://eventor.orientering.se/api/documentation>https://eventor.orientering.se/api/documentation</a>
</p>
</form>
</html>
