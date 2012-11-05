<?php

function getUserDetails($user_id) {

	$return = array();

	if (!is_numeric($user_id)) return $return;

	$return["user_id"] = $user_id;
	
	$query = "
	SELECT
	CONCAT_WS(' ', val_user.user_forename, val_user.user_surname) AS user_name,
	val_user.user_forename,
	val_user.user_surname,
	val_user.user_title_id,
	val_email.email_id,
	val_email.email_name,
	att_user_password.password,
	till.till_id,
	till.number,
	till.shop_id,
	shop.*,
	live_location.name AS live_location_name,
	hold_location.name AS hold_location_name
	FROM
	val_user
	NATURAL LEFT JOIN
	att_user_password
	NATURAL LEFT JOIN
	lkp_user_email
	NATURAL LEFT JOIN
	val_email
	LEFT JOIN till USING (user_id)
	LEFT JOIN shop USING (shop_id)
	LEFT JOIN location AS live_location ON (shop.live_location_id = live_location.location_id)
	LEFT JOIN location AS hold_location ON (shop.hold_location_id = hold_location.location_id)
	WHERE
	val_user.user_id = ".$user_id."
	GROUP BY user_id";
	
	$result = mysql_query($query) or die("couldn't execute query: ".$query);

	if (mysql_num_rows($result)<1) return $return;

	$row = mysql_fetch_array($result);

	foreach ($row as $key => $value) {

		$return[$key] = $value;

	}

	mysql_free_result($result);
	
	//ADD ROLES
	$return['roles'] = array();
	if ($res = mysql_query('SELECT role_id FROM lkp_user_role WHERE user_id = ' . $user_id)) {
	
		while ($row = mysql_fetch_assoc($res)) {
			$return['roles'][$row['role_id']] = new Role($row['role_id']);
		}
	}
	
	//ADD ACCESS
	$return['access'] = array();
	if ($res = mysql_query('SELECT access_id FROM lkp_user_access WHERE user_id = ' . $user_id)) {
	
		while ($row = mysql_fetch_assoc($res)) {
			$return['access'][$row['access_id']] = $row['access_id'];
		}
	}
	
	// ADD USER SIZES
	$return['size'] = array();
	
	if($get = mysql_query("SELECT bodypart_id FROM val_bodypart")) {
		
		while($row = mysql_fetch_assoc($get)) {
			
			$size = null;
			
			if($my_size = mysql_query("SELECT size_id FROM lkp_user_size WHERE user_id = ".$user_id." AND bodypart_id = ".$row['bodypart_id']." ORDER BY amount DESC LIMIT 1")) {
				
				if(mysql_num_rows($my_size) > 0) {
					$size = new Size(mysql_result($my_size, 0));
				}
				
			}
			
			$return['size'][$row['bodypart_id']] = $size;
			
		}
		
	}
	
	if ($return["email_id"]) {

		$query = "
		SELECT
		*
		FROM
		att_email_list
		WHERE
		email_id = ".$return["email_id"];
	
		$result = mysql_query($query) or die("couldn't execute query: ".$query);
	
		if (mysql_num_rows($result)>0) {
	
			$return["emailupdates"] = "yes";
	
		}
	
		mysql_free_result($result);

	}
	
	$query = "
	SELECT
	*
	FROM
	att_user_postalupdates
	WHERE
	user_id = ".(int) $user_id;
	
	$result = mysql_query($query) or die("couldn't execute query: ".$query);
	
	if (mysql_num_rows($result)>0) {
	
	    $return["postalupdates"] = "yes";
	
	}
	
	mysql_free_result($result);
	
	

	$query = "
	SELECT
	notes
	FROM
	att_user_notes
	WHERE
	user_id = ".$user_id;

	$result = mysql_query($query) or die("couldn't execute query: ".$query);

	$row = mysql_fetch_array($result);
	$return["notes"] = $row["notes"];

	mysql_free_result($result);
	
	$query = "
	SELECT
	telephone_name
	FROM
	lkp_user_telephone
	NATURAL LEFT JOIN
	val_telephone
	WHERE
	address_id IS NULL
	AND user_id = ".$user_id;

	$result = mysql_query($query) or die(mysql_error());//"couldn't execute query: ".$query);

	$row = mysql_fetch_array($result);
	$return["telephone_name"] = $row["telephone_name"];

	mysql_free_result($result);

	$query = "
	SELECT DISTINCT
	lkp_user_address.billing,
	lkp_user_address.delivery,
	val_address.address_id,
	val_address.address_name,
	val_address.address_name_1,
	val_address.address_name_2,
	val_address.address_town,
	val_address.address_state_id,
	CONCAT(state_name, ' ', val_state.state_id) AS state_name, 
	val_country.country_id,
	val_country.country_name,
	lkp_region_currency.currency_id
	FROM
	lkp_user_address
	NATURAL LEFT JOIN
	val_address
	NATURAL LEFT JOIN
	lkp_address_country
	NATURAL LEFT JOIN
	val_country
	NATURAL LEFT JOIN
	lkp_country_region
	NATURAL LEFT JOIN
	lkp_region_currency
	LEFT JOIN val_state ON (val_address.address_state_id = val_state.state_id) AND (lkp_address_country.country_id = val_state.country_id)
	WHERE
	lkp_user_address.user_id = ".$user_id;

	$billing_sql = " AND lkp_user_address.billing ='Y' ";
	
	// firstly check to get billing address. Due to a legacy bug  
		
	$result = mysql_query($query . $billing_sql) or die("couldn't execute query: ".$query);

	if (mysql_num_rows($result)<1) 
	{
	// no billing address found
	// See if any address is available ( without using billing check )
		$result = mysql_query($query) or die("couldn't execute query: ".$query);
	
		if (mysql_num_rows($result)<1) return $return;
	 
	}
		
	$row = mysql_fetch_array($result);

	foreach ($row as $key => $value) {

		if(!empty($value)) $return[$key] = $value;

	}
	
	mysql_free_result($result);
	
	$country = mysql_query("SELECT country_id 
							
							FROM lkp_user_address
							
							JOIN val_address USING (address_id)
							JOIN lkp_address_country USING (address_id)
							
							WHERE billing = 'Y' AND user_id = ".(int) $user_id);
	
	if(mysql_num_rows($country) != 0) $return['country_of_residence'] = mysql_result($country, 0);
	else {
		$country = mysql_query("SELECT country_id 
								
								FROM lkp_user_address
								
								JOIN val_address USING (address_id)
								JOIN lkp_address_country USING (address_id)
								
								WHERE delivery = 'Y' AND user_id = ".(int) $user_id.
								
								" ORDER BY lkp_user_address.address_id DESC ");
		
		if(mysql_num_rows($country)) $return['country_of_residence'] = mysql_result($country, 0);
	}
	
	if (empty($return["address_id"])) return $return;

	$query = "
	SELECT
	postcode
	FROM
	att_address_postcode
	WHERE
	address_id = ".$return["address_id"];

	$result = mysql_query($query) or die("couldn't execute query: ".$query);

	if (mysql_num_rows($result)>0) {

		$row = mysql_fetch_array($result);
		$return["postcode"] = $row["postcode"];

	}

	mysql_free_result($result);
	
	
	return $return;

}