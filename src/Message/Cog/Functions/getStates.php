<?php



function getStates($countryID = NULL) {
	$states = array();
	$query = 'SELECT country_id, state_id, state_name '
		   . 'FROM val_state '
		   . 'ORDER BY country_id DESC, state_name ASC';
	$DB = new DBquery($query);
	while ($row = $DB->row()) {
		$states[$row['country_id']][$row['state_id']] = $row['state_name'] . ' ' . $row['state_id'];
		$states['ALL'][$row['country_id'] . '_' . $row['state_id']] = $row['country_id'] . ' - ' . $row['state_name'] . ' ' . $row['state_id'];
	}
	if (isset($states[$countryID])) {
		return $states[$countryID];
	}
	return $states;
} 


?>