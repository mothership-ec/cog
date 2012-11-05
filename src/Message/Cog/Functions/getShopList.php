<?php

function getShopList() {
	
	$DB = new DBquery;

	$DB->query('SELECT shop_id, name '
			.  'FROM shop '
			.  'ORDER BY name ASC');
	
	$return = array();

	if($DB->numrows() > 0) {
		while($row = $DB->row()) {
			$return[$row['name']] = $row['shop_id'];
		}
	}

	return $return;

}