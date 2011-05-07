<?php
  define('EVENTOR_API_KEY', "8ebc1e96796547518d68a8b37059e95e");
  define('EVENTOR_API_BASE_URL', "https://eventor.orientering.no/api/");
  define('EVENTOR_ORGANISATION_ID', 245); //

  # Caching
  define('CACHE', dirname(__FILE__) . '/cache/');

  function eventorApiCall($url)
  {
    // create curl resource
    $ch = curl_init();
    // set url
    curl_setopt($ch, CURLOPT_URL, $url);
    // return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // set header
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("ApiKey: " . EVENTOR_API_KEY));

    // $output contains the output string
    $output = curl_exec($ch);

    // close curl resource to free up system resources
    curl_close($ch);

    return $output;
  }
 
  function getEventorPersons()
  {
    $url = EVENTOR_API_BASE_URL ."persons/organisations/". EVENTOR_ORGANISATION_ID;
    $xml = eventorApiCall($url);
   
    $persons = array();
   
    $doc = simplexml_load_string($xml);
    $personNodes = $doc->Person;
   
    foreach($personNodes as $personNode)
    {
      $firstName = $personNode->PersonName->Given;
      $lastName = $personNode->PersonName->Family;
      $personId = $personNode->PersonId;
      $dateOfBirth = $personNode->BirthDate->Date;
      $persons[] = array("FirstName" => $firstName, "LastName" => $lastName, "PersonId" => $personId, "DateOfBirth" => $dateOfBirth);
    }
    return $persons;
  }
?>