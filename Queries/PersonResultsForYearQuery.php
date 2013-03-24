<?php
class PersonResultsForYearQuery extends Query
{
	public function getSupportedParameters()
	{
		return array('personid' => 0, 'y' => 2012);		
	}
	
	protected function getQueryUrl()
	{
		$values = $this->getParameterValues();
		$y = $values['y'];
		$personId = $values['personid'];
		
		$url = "results/person?personId=$personId&fromDate=$y-01-01&toDate=$y-12-31";
				
		return $url;
	}

	protected function formatHtml($xml)
	{							
		$doc = simplexml_load_string($xml);				
		$resultListList = $doc;
		
		if(count($resultListList) == 0)
		{
			return '<h3>Ingen resultater funnet</h3>';
		}
		
		$personName = $resultListList[0]->ResultList->ClassResult->PersonResult->Person->PersonName;
		$lastname = $personName->Family;
		$firstname = $personName->Given;
			
		$clubId = $resultListList[0]->ResultList->ClassResult->PersonResult->Organisation->OrganisationId;
		
		//if($clubId != $this->getOrgId())
		//{
		//	return '<h3>Kan ikke hente for personer fra annen klubb</h3>';
		//}	
							
		$html = "<h2>$firstname $lastname</h2>";
		
		$currentYear = date('Y');	
		$values = $this->getParameterValues();
		$personId = $values['personid'];
		$yearFromQueryParameter = $values['y'];

		for($i=2011; $i <= $currentYear; $i++)
		{
			if($i == $yearFromQueryParameter)			
				$link = "<b>$yearFromQueryParameter</b>&nbsp;";
			else
				$link = "<a href='?personid=$personId&y=$i'>$i</a>&nbsp;";	
				
			$html .= $link;			
		}
		
		$html .= '<table><th></th><th></th><th></th><th>Nr</th><th>Tid</th><th></th><th>Status</th>';
		
		foreach ($resultListList->ResultList as $resultList) 
		{
			$eventId = $resultList->Event->EventId;
			$eventName = $resultList->Event->Name;
			$eventDate = $resultList->Event->StartDate->Date;
			
			$class = $resultList->ClassResult->EventClass->ClassShortName;
			$noOfStarts = $resultList->ClassResult->EventClass->ClassRaceInfo['noOfStarts'];
			$time = $resultList->ClassResult->PersonResult->Result->Time;
			
			$timediff = $resultList->ClassResult->PersonResult->Result->TimeDiff;
			if(!empty($timediff))
			{
				$timediff = '+'.$timediff;
			}
			
			$resultPosition = $resultList->ClassResult->PersonResult->Result->ResultPosition;
			$competitorStatus = $resultList->ClassResult->PersonResult->Result->CompetitorStatus['value'];
			
			if($competitorStatus == 'OK')
				$competitorStatus = '';
			
			$html .= "<tr><td>$eventDate</td><td>$eventName</td><td>$class</td><td align='right'>$resultPosition</td><td align='right'>$time</td><td align='right'>$timediff</td><td>$competitorStatus</td></tr>";
		}
		
		$html .= '</table>';	
		
		return $html;		
	}
}
?>