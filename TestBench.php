<html>
<form>

<?php
 // usage: http://url/wp-content/plugins/EventorPlugin/TestBench.php


// Stub out the add_action function since EventorPlugin.php hook up top Wordpress with it.
function add_action($a1, $a2){}

// Require the real plugin code
require_once ('EventorPlugin.php');

// All valid function names must be inserted here.
$validFunctions = array('Activities', 'Results');

function formatXmlString($xml) {  
  
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

function printActionControls()
{
	global $validFunctions;

	$action = $_GET[action];

	echo '<select name=action>';
	
	// echo all option values
	foreach ($validFunctions as $i => $value) 
	{
		$selected = '';
		
		if ($action == $value)
			$selected = 'selected';
		
		echo '<option '.$selected.'>'.$value.'</option>';
	}
	echo '</select>';
	echo "<input type=submit value=Vis />";
	echo '<br/>';
}

function doTest()
{
	$action = $_GET[action];

	if (empty($action))
		return;

	$xmlFunc = 'get'.$action.'FromEventor';
	$htmlFunc = 'make'.$action.'Html';
	
	$xml = $xmlFunc();
	$html = $htmlFunc($xml);
	
	$xmlString = formatXmlString($xml);

	echo "XML<br /><textarea rows=30 cols=150>$xmlString</textarea>";	
	echo "<br />HTML<br /><textarea rows=10 cols=150>$html</textarea>";
	echo "<hr />Preview <br />$html";
}

printActionControls();
doTest();
?>
</form>
</html>