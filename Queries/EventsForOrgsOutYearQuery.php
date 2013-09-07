<?php
class EventsForOrgsOutYearQuery extends EventUtilsQuery
{
	public function getSupportedParameters()
	{
		return array('orgids' => $this->getOrgId(), 'day' => date('d'), 'month' => date('m'), year => date('Y'), 'classificationids' => '', 'numdays' => '');
	}
	
	protected function getQueryUrl()
	{
		$values = $this->getParameterValues();
		
		$orgIds = $values['orgids'];
		$day = $values['day'];
		$month = $values['month'];
		$year = $values['year'];
		$classificationIds = $values['classificationids'];
		$numDays = $values['numdays'];
		
		$url = "events?fromDate=$year-$month-$day";
		
		if ($orgIds != "") {
			$url .= "&organisationIds=$orgIds";
		}

		if ($classificationIds != "") {
			$url .= "&classificationIds=$classificationIds";
		}
		
		if ($numDays != "") {
			$fromDate = date("Y-m-d");
			echo $fromDate;
			$toDate = date("Y-m-d", strtotime("+$numDays days", strtotime($fromDate)));
			echo $toDate;
			$url .= "&toDate=$toDate";
		} else {
			$url .= "&toDate=$year-12-31";
		}
		echo $url;
		return $url;
	}
	
	protected function formatHtml($xml)
	{
		$doc = simplexml_load_string($xml);

		$data = '<ul>';

		foreach ($this->sortEvents($doc->Event) as $event)
		{
			$eventId = $event->EventId;
			$name = $event->Name;
				
			$eventorUrl = $this->getEventorBaseUrl() . '/Events/Show/'.$eventId;

			$eventDate = $event->StartDate->Date;

			$name = htmlentities($name);
			$eventDate = new DateTime($eventDate);
			$eventDate = $eventDate->format('j/n');

			$data .= "<li><a href=\"" . $eventorUrl . "\">" . $name . "</a> - " . $eventDate . "</li>";
		}

		$data .= '</ul>';

		return $data;
	}
}
?>