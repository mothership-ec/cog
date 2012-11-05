<?php

function deleteGiftCertificate($id) {
	$ids = (array) $id;

/*
$query = 'DELETE val_gift, att_gift_price '
		   . 'FROM val_gift '
		   . 'JOIN att_gift_price USING (gift_id) '
		   . "WHERE val_gift.gift_id IN ('" 
		   . implode("','", $ids) . "')";
*/
	$query = "UPDATE val_gift SET expiry_date = NOW() WHERE gift_id IN ('".implode("','", $ids)."')";

	return DBquery($query);
}



?>