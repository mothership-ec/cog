<?php

function sendEmailRequest($email) {

	$query = "
	SELECT
	*
	FROM
	val_email,
	att_email_list
	WHERE
	val_email.email_id = att_email_list.email_id
	AND
	val_email.email_name = '".addslashes($email)."'
	";

	$result = mysql_query($query) or die("Couldn't execute query: ".$query);

	if (mysql_num_rows($result)>0) return false;

	mysql_free_result($result);


	$subject = "Welcome to ".Config::get('merchant')->name.", please visit the link in the email to confirm your registration";

	$from = Config::get('merchant')->email;

	$body = "Thank you for registering to receive updates from ".Config::get('merchant')->name.".\n\n";
	$body.= "To complete your registration please click here:\n";
	$body.= "http://".Config::get('merchant')->url."/confirm/?confirm=".md5($email)."\n\n";
	$body.= "If you do not wish to receive updates from ".Config::get('merchant')->name." please ignore this email.\n\n";
	$body.= "You can remove yourself from our mailing list at any time by visiting:\n";
	$body.= "http://".Config::get('merchant')->url."/account/\n\n";
	$body.= "--\n";

	if (!mail($email,$subject,$body,"FROM: ".$from)) return false;

	return true;

}