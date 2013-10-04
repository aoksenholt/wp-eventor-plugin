<?php
class AddressListQuery extends Query
{
	public function getSupportedParameters()
	{
		return array('orgid' => $this->getOrgId());		
	}
	
	protected function getQueryUrl()
	{
		$values = $this->getParameterValues();
		
		$orgId = $values['orgid'];		
		
		if (empty($orgId))
		{
			$orgId = '0';
		}
			
		$url = 'persons/organisations/'.$orgId.'?includeContactDetails=true';
		
		return $url;
	}

	protected function formatHtml($xml)
	{							
		$doc = simplexml_load_string($xml);				
		$personList = $doc;
		
		$arr = array();

		foreach ($personList->Person as $person) 
		{
			$firstname = $person->PersonName->Given;
			$lastname = $person->PersonName->Family;
			$name = "$lastname, $firstname";
			
			$arr[(string)$name] = $person;
		}
		ksort($arr);
		
		$html = '<table><th>Navn</th><th>E-post</th><th>Telefon</th><th>Adresse</th><th>Oppdatert</th>';
				
		foreach ($arr as $person)
		{
			$firstname = $person->PersonName->Given;
			$lastname = $person->PersonName->Family;
			
      $address = $person->Address;      
			$addressText = $address['street'];
      
      if(!empty($addressText))
        $addressText .= ', '.$address['zipCode'].' '.$address['city'];
			
      $tele = $person->Tele;
      
      $email = $tele['mailAddress'];
      
      //$email = str_replace('@', '[at]', $email);
      
      $mobile = $tele['mobilePhoneNumber'];
      $phone = $tele['phoneNumber'];
      
      $modified = $tele->ModifyDate->Date;
      
      if(!empty($mobile))
        $phone = $mobile;
			
			$html .= "<tr><td>$lastname, $firstname</td><td>$email</td><td>$phone</td><td>$addressText</td><td>$modified</td></tr>";
		}
		$html .= '</table>';		

		return $html;		
	}
}
?>