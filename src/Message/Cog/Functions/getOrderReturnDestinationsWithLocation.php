<?php

function getOrderReturnDestinationsWithLocation() {
	
	$destinations = array();

	$DB = new DBquery('SELECT return_destination_id, return_destination_name, location_id, location.name AS location_name '
					. 'FROM order_return_destination '
					. 'LEFT JOIN location USING (location_id) '
					. 'ORDER BY return_destination_id ASC');
	
	if($DB->result()) {
		while($row = $DB->row()) {
			$destinations[$row['return_destination_id']] = $row;
		}
	}

	return $destinations;

}
