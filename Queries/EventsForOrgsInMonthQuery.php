<?php
class EventsForOrgsInMonthQuery extends EventUtilsQuery
{
	public function getSupportedParameters()
	{
		return array('orgids' => $this->getOrgId(), 'day' => date('d'), 'month' => date('m'), year => date('Y'));
	}
	
	protected function getQueryUrl()
	{
		$values = $this->getParameterValues();
		
		$orgIds = $values['orgids'];
		$day = $values['day'];
		$month = $values['month'];
		$year = $values['year'];

		if ($orgIds != "") {
			$url = "events?organisationIds=$orgIds&fromDate=$year-$month-$day&toDate=$year-$month-31";
		} else {
			$url = "events?fromDate=$year-$month-$day&toDate=$year-$month-31";
		}
		
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