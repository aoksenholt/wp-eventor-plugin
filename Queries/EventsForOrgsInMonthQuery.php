<?php
class EventsForOrgsInMonthQuery extends Query
{
	public function getSupportedParameters()
	{
		return array('orgids' => $this->getOrgId(), 'month' => '06');
	}
	
	protected function getQueryUrl()
	{
		$values = $this->getParameterValues();
		
		$orgIds = $values['orgids'];
		$month = $values['month'];
		
		$url = "events?organisationIds=$orgIds&fromDate=2011-$month-01&toDate=2011-$month-31";

		return $url;
	}
	
	protected function formatHtml($xml)
	{
		$events = array();

		$doc = simplexml_load_string($xml);
		$eventNodes = $doc->Event;

		$data = '<ul>';

		foreach ($doc->Event as $event)
		{
			$eventId = $event->EventId;
			$name = utf8_decode($event->Name);

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