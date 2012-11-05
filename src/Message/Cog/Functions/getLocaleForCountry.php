<?php



function getLocaleForCountry($countryID) {
	$db = new DBquery;
	$query = 'SELECT locale_id '
		   . 'FROM lkp_region_locale '
		   . 'JOIN lkp_country_region USING (region_id) '
		   . 'WHERE country_id = ' . $db->escape($countryID);
	if ($db->query($query)) {
		return $db->value();
	}
	throw new Exception('country ' . $countryID . ' not recognised, can\'t find locale id');
}