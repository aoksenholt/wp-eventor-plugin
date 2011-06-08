<?php
class EventsFromOptionListQuery extends Query
{
	protected function getQueryUrl()
	{
		// Fetch one year ahead
		$fromDate = date("Y-m-d");
		$toDate = date("Y-m-d", strtotime("+1 year", strtotime($fromDate)));

		$url = "events?eventIds=" . get_option(MT_EVENTOR_EVENTIDS) . "&from=" . $fromDate . "&to=" . $toDate . "&includeEntryBreaks=true";

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