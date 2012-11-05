<?php

function getSizes() {
	
	$DB = new DBquery;

	$DB->query('SELECT size_id, string_value '
			.  'FROM val_size '
			.  'JOIN locale_string USING (string_id) '
			.  'WHERE locale_id = '.$DB->escape(Locale::DEFAULT_LOCALE_ID));
	
	$return = array();

	if($DB->numrows() > 0) {
		while($row = $DB->row()) {
			$return[$row['size_id']] = $row['string_value'];
		}
	}
	
	asort($return);

	return $return;

}