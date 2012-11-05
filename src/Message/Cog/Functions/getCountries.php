<?php

function getCountries($mode = false) {
	
	$query = 'SELECT country_id, country_name FROM val_country '.($mode ? 'WHERE show_'.$mode.' = 1' : '').' ORDER BY country_name';

	if ($res = new DBquery($query)) {

		while($row = $res->row()) {
			$countries[$row['country_id']] = $row['country_name'];
		}
	}

	return $countries;

}