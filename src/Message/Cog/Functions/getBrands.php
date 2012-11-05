<?php

function getBrands($suppliers_only = false) {
	
	$DB = new DBquery;

	$DB->query('SELECT brand_id, brand_name '
			.  'FROM brand '
			.  'JOIN brand_info USING (brand_id) '
			.	($suppliers_only ? 'JOIN supplier_brand USING (brand_id) ' : '')
			.  'WHERE locale_id = '.$DB->escape(Locale::instance()->getId())
			.  'ORDER BY brand_name ASC');
	
	$return = array();

	if($DB->numrows() > 0) {
		while($row = $DB->row()) {
			$return[$row['brand_id']] = $row['brand_name'];
		}
	}

	return $return;

}