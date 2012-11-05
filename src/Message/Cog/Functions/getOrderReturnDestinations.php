<?php

function getOrderReturnDestinations() {
	
	$destinations = array();

	$query = 'SELECT return_destination_id, return_destination_name FROM order_return_destination ORDER BY return_destination_id ASC';
	if ($res = new DBquery($query)) {
		foreach ($res->rows() as $row) {
			$destinations[$row['return_destination_id']] = $row['return_destination_name'];
		}
	}
	return $destinations;
}



?>