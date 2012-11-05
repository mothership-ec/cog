<?php



function getUserAddress($userID, $type = 'billing') {
	
	$address = array();
	
	//EITHER THE DELIVERY ADDRESS..
	$query1 = 'SELECT val_address.address_id, address_name, address_name_1, address_name_2, address_town, address_state_id AS state_id, state_name, title_name, country_name, telephone_name, val_telephone.telephone_id, '
		    . 'val_country.country_id, postcode, address_title_id AS user_title_id, address_forename AS user_forename, address_surname AS user_surname, 0 AS billing, '
			. "CONCAT(locale.locale_id, ':', locale.currency_id) AS currency_id "
		    . 'FROM lkp_user_address '
		    . 'JOIN val_address USING (address_id) '
		    . 'JOIN lkp_address_country USING (address_id) '
		    . 'JOIN att_address_postcode USING (address_id) '
		    . 'LEFT JOIN att_address_name USING (address_id) '
		    . 'JOIN val_title ON (address_title_id = title_id) '
			. 'JOIN val_country ON (lkp_address_country.country_id = val_country.country_id) '
			. 'JOIN lkp_country_region ON (lkp_country_region.country_id = val_country.country_id) '
			. 'JOIN lkp_region_locale USING (region_id) '
			. 'JOIN locale USING (locale_id) '
			. 'LEFT JOIN lkp_user_telephone ON (lkp_user_address.user_id = lkp_user_telephone.user_id AND lkp_user_address.address_id = lkp_user_telephone.address_id) '
			. 'LEFT JOIN val_telephone ON (lkp_user_telephone.telephone_id  = val_telephone.telephone_id) '
			. 'LEFT JOIN val_state ON (address_state_id = val_state.state_id AND lkp_address_country.country_id = val_state.country_id) '
			. "WHERE delivery = 'Y' AND billing = 'N' "
		    . 'AND lkp_user_address.user_id = ' . (int) $userID
		    . ' ORDER BY address_ID DESC';

	//OR THE BILLING ADDRESS IF NONE SET
	$query2 = 'SELECT val_address.address_id, address_name, address_name_1, address_name_2, address_town, address_state_id AS state_id, state_name, title_name, country_name, telephone_name, val_telephone.telephone_id, '
		    . 'val_country.country_id, postcode, user_title_id, user_forename, user_surname, 1 AS billing, '
		    . "CONCAT(locale.locale_id, ':', locale.currency_id) AS currency_id "
			. 'FROM val_user '
			. 'JOIN lkp_user_address USING (user_id) '
		    . 'JOIN val_address USING (address_id) '
		    . 'JOIN lkp_address_country USING (address_id) '
		    . 'JOIN att_address_postcode USING (address_id) '
			. 'JOIN val_country ON (lkp_address_country.country_id = val_country.country_id) '
			. 'JOIN lkp_country_region ON (lkp_country_region.country_id = val_country.country_id) '
			. 'JOIN lkp_region_locale USING (region_id) '
			. 'JOIN locale USING (locale_id) '
			. 'LEFT JOIN val_title ON (user_title_id = val_title.title_id) '
			. 'LEFT JOIN lkp_user_telephone ON (val_user.user_id = lkp_user_telephone.user_id) '
			. 'LEFT JOIN val_telephone ON (lkp_user_telephone.telephone_id = val_telephone.telephone_id) '
			. 'LEFT JOIN val_state ON (address_state_id = val_state.state_id AND lkp_address_country.country_id = val_state.country_id) '
			. "WHERE billing = 'Y' "
			. 'AND lkp_user_address.user_id = ' . (int) $userID .' '
			. 'ORDER BY telephone_id DESC';

	if ($type == 'billing') {
		if ($res = new DBquery($query2)) {
			$address = $res->row();
		}
	} elseif ($type == 'delivery') {
		if ($res = new DBquery($query1)) {
			if(!$res->numRows()) {
				$res = new DBquery($query2);
			}
			$address = $res->row();
			if ($address['billing']) {
				$address['address_id'] = 0;
			}
			
		}
	}
	
	return $address;
}




?>