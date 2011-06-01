<?php
class ActivityDeadlinesQuery extends Query
{
	protected function getQueryUrl()
	{
		// Fetch one year ahead
		$fromDate = date("Y-m-d");
		$toDate = date("Y-m-d", strtotime("+1 year", strtotime($fromDate)));

		$url = "activities?organisationId=" . $this->getOrgId() . "&from=" . $fromDate . "&to=" . $toDate . "&includeRegistrations=false";

		return $url;
	}

	protected function formatHtml($xml)
	{
		$activities = array();

		$doc = simplexml_load_string($xml);
		$activityNodes = $doc->Activity;

		$data = '<ul>';

		foreach ($doc->Activity as $activity)
		{
			$name = $activity->Name;
			$url = $activity['url'];
			$numRegistrations = $activity['registrationCount'];
			$registrationDeadline = $activity['registrationDeadline'];

			$name = htmlentities($name);
			$date = new DateTime($registrationDeadline);
			$registrationDeadline = $date->format('j/n H:i');

			$data .= "<li><a href=\"" . $url . "\">" . $name . "</a> (" . $numRegistrations . ") - " . $registrationDeadline . "</li>";
		}

		$data .= '</ul>';

		return $data;
	}
}
?>