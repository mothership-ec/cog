<?php

function getColours() {
	
	$DB = new DBquery;

	$DB->query('SELECT colour_id, string_value '
			.  'FROM val_colour '
			.  'JOIN locale_string USING (string_id) '
			.  'WHERE locale_id = '.$DB->escape(Locale::DEFAULT_LOCALE_ID));
	
	$return = array();

	if($DB->numrows() > 0) {
		while($row = $DB->row()) {
			$return[$row['colour_id']] = $row['string_value'];
		}
	}
	
	asort($return);

	return $return;

}