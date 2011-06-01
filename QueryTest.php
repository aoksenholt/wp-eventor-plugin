<div class="wrap">
<h2>Query Test</h2>
<p>
This page is for debugging queries during development. Submit the query type and inspect the xml, html and the rendering.
</p>
<form method="post">
<?php
require_once 'DebugFunctions.php';

function runQuery()
{
	$queryType = '';
	
	if(isset($_POST['querytype']))
	{
		$queryType = $_POST['querytype'];
	}
	echo 'Query <input type="text" name="querytype" size="50" value="' . $queryType .'" />';
	echo '<p class="submit"><input type="submit" value="Submit" /></p>';
	echo '<br/>';

	if (empty($queryType))
	{
		return;
	}
	
	$query = new $queryType();

	$query->load();
	$xml = $query->getXml();
	$html = $query->getHtml();

	$xmlString = formatXmlString($xml);

	echo 'Eventor Response<br /><textarea rows=30 cols=150>'.$xmlString.'</textarea>';
	echo '<br />Query Html<br /><textarea rows="10" cols="150">'.$html.'</textarea>';
	echo '<hr />Preview <br />'.$html;
}
runQuery();
?>
</form>
</div>