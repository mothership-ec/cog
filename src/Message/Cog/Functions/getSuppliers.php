<?php

function getSuppliers() {
	
	$DB = new DBquery;

	$DB->query('SELECT supplier_id '
			.  'FROM supplier '
			.  'ORDER BY supplier_name ASC');
	
	$return = array();

	if($DB->numrows() > 0) {
		while($row = $DB->row()) {
			$return[$row['supplier_id']] = new Supplier($row['supplier_id']);
		}
	}

	return $return;

}