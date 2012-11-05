<?php

#	This function performs a lookup on one table.
#	Pass this function the field you're searching for, the table that the field you're searching for is in and the name and value of the key in that table.
#	This function returns a string with the value.


function getValueFromTable($table,$field,$key,$value) {

	$return = "";

	$query = "
	SELECT
	".$field."
	FROM
	".$table."
	WHERE
	".$key." = '".addslashes($value)."'
	LIMIT 1
	";

	$result = mysql_query($query);

	if (mysql_num_rows($result)>0) {

		$row = mysql_fetch_array($result);
	
		$return = $row[$field];

	}

	mysql_free_result($result);

	return $return;

}