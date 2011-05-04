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

	echo "XML<br /><textarea rows=30 cols=150>$xml</textarea>";	
	echo "<br />HTML<br /><textarea rows=10 cols=150>$html</textarea>";
	echo "<hr />Preview <br />$html";
}

printActionControls();
doTest();
?>
</form>
</html>