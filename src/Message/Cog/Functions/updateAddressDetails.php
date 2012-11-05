<?php

#	This function edits details in the database related to an address.
#	This function takes two arguments:
#	An array of values (the keys must match the keys in the database)
#	The id of the address.
#	The function returns true or false.
#	This function is used by updateUserDetails.

function updateAddressDetails($details,$address_id) {

	if (!is_numeric($address_id)) return false;

	$queries = array();

	$fields = array(
	'address_name_1' => "val_address",
	'address_name_2' => "val_address",
//	"user_id"		=>	"lkp_user_address",
	"billing"		=>	"lkp_user_address",
	"delivery"		=>	"lkp_user_address",
	"postcode"		=>	"att_address_postcode",
	"country_id"	=>	"lkp_address_country"
	);

	foreach ($fields as $field => $table) {

		if (isset($details[$field])) {

			$query = "
			SELECT
			*
			FROM
			".$table."
			WHERE
			address_id = ".$address_id;
	
			$result = mysql_query($query) or die("Couldn't execute query: ".$query);
	
			if (mysql_num_rows($result) < 1) {
	
				$newquery = "
				INSERT INTO
				".$table."
				(address_id,".$field.")
				VALUES
				(".$address_id.",'".addslashes($details[$field])."')
				";

			} else {

				$newquery = "
				UPDATE
				".$table."
				SET
				".$field." = '".addslashes($details[$field])."'
				WHERE
				address_id = ".$address_id;

			}

			mysql_free_result($result);

			mysql_query($newquery) or die("Couldn't execute query: ".$newquery);

		}
	}

	return true;

}