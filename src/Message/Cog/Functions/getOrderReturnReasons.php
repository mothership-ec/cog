<?php


function getOrderReturnReasons($onlyEnabled=true) {
	
	$reasons = array();

	$query = 'SELECT return_reason_id, return_reason_name FROM order_return_reason';
	$query .= ($onlyEnabled ? ' WHERE enabled = 1' : '');	
	$query .= ' ORDER BY return_reason_id ASC';

	if ($res = new DBquery($query)) {
		
		foreach ($res->rows() as $row) {
			$reasons[$row['return_reason_id']] = $row['return_reason_name'];
		}
	}

	return $reasons;
}



?>