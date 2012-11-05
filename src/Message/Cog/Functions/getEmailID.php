<?php

#	This function retrieves an email_id associated with an email address
#	This function takes one argument:
#	The value of the email
#	The function returns the id of the email or zero.
#	The function is used by updateUserDetails

function getEmailID($email) {

	$return = null;

	$query = "
	SELECT
	email_id
	FROM
	val_email
	WHERE
	email_name = '".stripslashes($email)."'
	";

	$result = mysql_query($query);

	if (mysql_num_rows($result)>0) {

		$row = mysql_fetch_array($result);

		$return = $row["email_id"];

	}

	mysql_free_result($result);

	return $return;

}