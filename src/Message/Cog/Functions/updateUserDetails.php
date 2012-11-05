<?php

#	This function edits (or creates) details in the database related to a user.
#	This function takes two arguments:
#	An array of values (the keys must match the keys in the database)
#	The id of the user (if this is empty, a new user will be created).
#	The function returns the id of the user.
#	You can also pass address details: the function calls updateAddressDetails.
#	The function also calls getEmailID to check for existing email addresses.

function updateUserDetails($details,$user_id="new") {

	if ($user_id == "new" && empty($details['user_name'])) return false;
	
	//STATE ID HAS A COUNTRY PREFIX, WE NEED TO REMOVE IT
	$details['state_id'] = NULL;
	if (isset($details['address_state_id'])) {
		$details['state_id'] = substr($details['address_state_id'], 3);
	}
	
	$queries = array();

	if ($user_id == "new") {
	
	
	
		$query = "
		INSERT INTO
		val_user
		(user_name, user_forename, user_surname, user_title_id, sign_up_date)
		VALUES
		('".addslashes($details['user_name'])."', '".addslashes($details['user_forename'])."', '".addslashes($details['user_surname'])."', ".(int) $details['user_title_id'].", NOW())
		";
		# JOE EDITED ABOVE
		mysql_query($query) or die("Couldn't execute query: ".$query);
		
		$user_id = mysql_insert_id();
		if(isset($details['howdidyouhear_id'])){
		$queries[] = "
		INSERT INTO
		att_user_howdidyouhear
		(user_id, howdidyouhear_id)
		VALUES
		(".$user_id.",'".addslashes($details['howdidyouhear_id'])."')
		";
		
		}
	
	}

	$details['user_id'] = $user_id;

	if (!empty($details['user_forename']) && !empty($details['user_surname']) && !empty($details['user_title_id'])) {

		$queries[] = "
		UPDATE
		val_user
		SET
		user_forename = '".addslashes($details['user_forename'])."',
		user_surname = '".addslashes($details['user_surname'])."',
		user_title_id = '".(int)$details['user_title_id']."'
		WHERE
		user_id = ".$user_id;
		
		

	}

	$attributes = array("password","notes");
	
	
	if(empty($details['password'])) {
		unset($attributes[0]);
	}
	
	foreach ($attributes as $attribute) {

		if (isset($details[$attribute])) {

			if (empty($details[$attribute])) {

				$queries[] = "
				DELETE
				FROM
				att_user_".$attribute."
				WHERE
				user_id = ".$user_id;


			} else {
		
				$query = "
				SELECT
				".$attribute."
				FROM
				att_user_".$attribute."
				WHERE
				user_id = ".$user_id;
				
				$result = mysql_query($query) or die("Couldn't execute query: ".$query);
				
				if($attribute == 'password') {
					$details[$attribute] = \Mothership\Framework\Services::instance()->get('password')->encrypt($details[$attribute]);
				}

				if (mysql_num_rows($result)<1) {
					
					$queries[] = "
					INSERT INTO
					att_user_".$attribute."
					(user_id,".$attribute.")
					VALUES
					(".$user_id.",'".addslashes($details[$attribute])."')
					";
				
				} else {
				
					$queries[] = "
					UPDATE
					att_user_".$attribute."
					SET
					".$attribute." = '".addslashes($details[$attribute])."'
					WHERE
					user_id = ".$user_id;
		
				}
		
				mysql_free_result($result);

			}
		}
	}

	$lookups = array("email","address");

	foreach ($lookups as $lookup) {

		if (isset($details[$lookup."_name"])) {

			if (empty($details[$lookup."_name"])) {

				//	Deleting an email or address

				//	Is there already one connected to this user?

				$query = "
				SELECT
				".$lookup."_id
				FROM
				lkp_user_".$lookup."
				WHERE
				user_id = ".$user_id;

				$result = mysql_query($query) or die("Couldn't execute query: ".$query);
	
				if (mysql_num_rows($result)>0) {

					//	If one exists, delete it

					$row = mysql_fetch_array($result);
					$lookup_id = $row[$lookup."_id"];

					/*
					$queries[] = "
					DELETE FROM
					val_".$lookup."
					WHERE
					".$lookup."_id = ".$lookup_id;
					*/

					if ($lookup == "email") {
	
						$queries[] = "DELETE FROM att_email_list WHERE email_id = ".$lookup_id;
						mysql_query("UPDATE att_email_modified SET email_modified = NOW() WHERE email_id = ".$lookup_id);

					}
				}

				mysql_free_result($result);

				//	Delete the connection to the user

				$queries[] = "
				DELETE
				FROM
				lkp_user_".$lookup."
				WHERE
				user_id = ".$user_id;

			} else {

				//	Adding an email or address

				//	Is there one connected to this user?

				$query = "
				SELECT
				".$lookup."_id
				FROM
				lkp_user_".$lookup."
				WHERE
				user_id = ".$user_id;
				
				if($lookup == 'address') $query .= " AND billing = 'Y'";
				
				$result = mysql_query($query) or die("Couldn't execute query: ".$query);
	
				if (mysql_num_rows($result)<1) {

					// Couldn't get a value

					// If the email address already exists (from refer a friend), use it
		
					if ($lookup == "email" && getEmailID($details[$lookup."_name"]) > 0 ) {

						$lookup_id = getEmailID($details[$lookup."_name"]);

					} else {

					//	If the value isn't listed, create it now

						$query = "
						INSERT INTO
						val_".$lookup."
						(".$lookup."_name".($lookup == 'address' ? ', address_town, address_state_id' : '').")
						VALUES
						('".addslashes($details[$lookup."_name"])."'".($lookup == 'address' ? ", '".addslashes($details['address_town'])."', '".addslashes($details['state_id'])."'" : "").")
						";
						
						mysql_query($query);
	
						$lookup_id = mysql_insert_id();

					}
	
					//	Create the connection to the user

					$query = "
					INSERT INTO
					lkp_user_".$lookup."
					(user_id,".$lookup."_id)
					VALUES
					(".$user_id.",".$lookup_id.")
					";
	
					mysql_query($query) or die("Couldn't execute query: ".$query);
	
				} else {

					//	Update the value
	
					$row = mysql_fetch_array($result);
	
					$lookup_id = $row[$lookup."_id"];
	
					$queries[] = "
					UPDATE
					val_".$lookup."
					SET
					".$lookup."_name = '".addslashes($details[$lookup."_name"])."'
					".($lookup == 'address' ? ", address_name_1 = '".addslashes($details['address_name_1'])."', address_name_2 = '".addslashes($details['address_name_2'])."', address_town = '".addslashes($details['address_town'])."', address_state_id = '".addslashes($details['state_id'])."'" : "")."
					WHERE
					".$lookup."_id = ".$lookup_id;
	
				}
	
				if ($lookup == "address") {
	
					updateAddressDetails($details,$lookup_id);
	
				}

				if ($lookup == "email" && !isset($details["emailupdates"])) {

					$queries[] = "DELETE FROM att_email_list WHERE email_id = ".$lookup_id;
					mysql_query("UPDATE att_email_modified SET email_modified = NOW() WHERE email_id = ".$lookup_id);


				}
			}
		}
	}

	if(isset($details['telephone_name']) && !empty($details['telephone_name'])) {
		
		$db = new DBquery;

		$queries[] = 'SET @USER_ID = '.(int) $user_id;
		$queries[] = 'SET @TELEPHONE_ID = LAST_INSERT_ID(NULL)'; // reset insert id
		
		$queries[] = 'INSERT IGNORE INTO val_telephone SET telephone_name = '.$db->escape($details['telephone_name']);
		
		$queries[] = 'SET @TELEPHONE_ID = IF(LAST_INSERT_ID(), LAST_INSERT_ID(), (SELECT telephone_id FROM val_telephone WHERE telephone_name = '.$db->escape($details['telephone_name']).'))'; // set var to insert id if it changed, else select the telephone row
		
		$queries[] = 'DELETE FROM lkp_user_telephone WHERE user_id = @USER_ID AND address_id IS NULL';
		$queries[] = 'REPLACE INTO lkp_user_telephone (user_id, telephone_id, address_id) VALUES (@USER_ID, @TELEPHONE_ID, NULL)';
		
	}
	
	$access = new AccessControl(false);
	if ($access->canChangeroles() && !empty($_REQUEST["user_id"])) {
		
		mysql_query("DELETE FROM lkp_user_access WHERE user_id = ".$_REQUEST["user_id"]);
		mysql_query("DELETE FROM lkp_user_role WHERE user_id = ".$_REQUEST["user_id"]);
		
		$references = array("access", "role");
	
		foreach ($references as $lookup) {
	
			if (isset($details[$lookup."_id"])) {
	
				$queries[] = "
				DELETE
				FROM
				lkp_user_".$lookup."
				WHERE
				user_id = ".$user_id;
	
				foreach ($details[$lookup."_id"] as $lookup_id) {
		
					$queries[] = "
					INSERT INTO
					lkp_user_".$lookup."
					(user_id,".$lookup."_id)
					VALUES
					(".$user_id.",".$lookup_id.")
					";
	
				}
			}
		}
		
	}



	foreach ($queries as $query) {

		mysql_query($query) or die("Couldn't execute query: ".$query);

	}

	if (isset($details["emailupdates"]) && isset($details["email_name"])) {

		sendEmailRequest($details["email_name"]);

	}
	
	mysql_query("DELETE FROM att_user_postalupdates WHERE user_id = ". (int) $user_id);
	
	if(isset($details['postalupdates'])) {
		mysql_query("INSERT INTO att_user_postalupdates SET user_id = ". (int) $user_id);
	}
	
	return $user_id;

}