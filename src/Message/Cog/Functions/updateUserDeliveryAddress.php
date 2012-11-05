<?php


function updateUserDeliveryAddress($userID, $data) {
	
	$address = array_merge(
		array(
			'address'   => NULL,
			'town'      => NULL,
			'stateID'   => NULL,
			'postcode'  => NULL,
			'telephone' => NULL
		), (array) $data);
	

	//$query = 'SELECT address_id FROM lkp_user_address '
	//	   . "WHERE delivery = 'Y' AND user_id = " . (int) $userID;
		   
	//$DB = new DBquery($query);
	//$addressID = $DB->value();
	/*
	 * 	if ($addressID) {
	
		$DB = new DBtransaction;
		
		$DB->add('UPDATE val_address SET '
			   . 'address_name = ' . $DB->escape($address['address']) . ','
			   . 'address_town = '         . $DB->escape($address['town']) . ','
			   . 'address_state_id = '     . $DB->escape($address['stateID']) . ' '
			   . 'WHERE address_id = ' . $addressID . ';');
		
		$DB->add('REPLACE INTO att_address_postcode (address_id, postcode) VALUES ('
			   . $addressID . ',' . $DB->escape($address['postcode']) . ');');
		
		$DB->run();
		
	}

	 */
	
	$address = getUserAddress($userID,"delivery");
	$addressID = $address['address_id'];	   		   
		   
	if ($addressID) {
	
		$DB = new DBtransaction;
		
		$DB->add('UPDATE val_address SET '
			   . 'address_name = ' . $DB->escape($address['address_name']) . ','
			   . 'address_town = '         . $DB->escape($address['address_town']) . ','
			   . 'address_state_id = '     . $DB->escape($address['state_id']) . ' '
			   . 'WHERE address_id = ' . $addressID . ';');
		
		$DB->add('REPLACE INTO att_address_postcode (address_id, postcode) VALUES ('
			   . $addressID . ',' . $DB->escape($address['postcode']) . ');');
		
		$DB->run();
		
	}
	
	
}




?>