<?php

function getTaxRates() {
	
	$tax = array();
	$DB = new DBquery("SELECT tax_code, tax_rate FROM order_tax WHERE country_id = 'GB' ORDER BY tax_rate DESC");
	while ($row = $DB->row('OBJECT')) {
		$tax[$row->tax_code] = (float) $row->tax_rate;
	}
	return $tax;

}