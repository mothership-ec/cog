<?php

function getLocations() {
	
	$DB = new DBquery;

	$DB->query('SELECT location_id, name '
			.  'FROM location '
			.  'ORDER BY name ASC');
	
	$return = array();

	if($DB->numrows() > 0) {
		while($row = $DB->row()) {
			$return[$row['location_id']] = $row['name'];
		}
	}

	return $return;

}