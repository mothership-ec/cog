<?php


function getOrderReturnResolutions() {
	
	// bump option 5 to the top of the list
	function sortOrderReturnResolutions($a, $b) {
		if($a == 5) return -1;
		return 1;
	}
	
	$query = 'SELECT return_resolution_id, return_resolution_name FROM order_return_resolution ORDER BY return_resolution_id ASC';
	if ($res = new DBquery($query)) {
		foreach ($res->rows() as $row) {
			$resolutions[$row['return_resolution_id']] = $row['return_resolution_name'];
		}
	}
	
	uksort($resolutions, 'sortOrderReturnResolutions');
	
	return $resolutions;
}



?>