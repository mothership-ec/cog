<?php

function getCurrencies() {
	
	$currencies = array();
	$DB = new DBquery("SELECT currency_id, currency_name FROM val_currency");
	while ($row = $DB->row('OBJECT')) {
		$currencies[$row->currency_id] = $row->currency_name;
	}
	return $currencies;

}