<?php

function getSizeBreaks() {
	
	$DB = new DBquery;

	$DB->query('SELECT sizebreak_name, sizebreak_id, string_value, size_id '
			.  'FROM val_sizebreak '
			.  'JOIN lkp_sizebreak_size USING (sizebreak_id) '
			.  'JOIN val_size USING (size_id) '
			.  'JOIN locale_string USING (string_id) '
			.  'WHERE locale_id = '.$DB->escape(Locale::DEFAULT_LOCALE_ID));
			
			
	$return = array();
	$lastID = NULL;
	if($DB->numrows() > 0) {
		while($row = $DB->row()) {
		
			if($lastID != $row['sizebreak_id']) {
				$return[$row['sizebreak_id']] = (object)array(
					'name'	=> $row['sizebreak_name'],
					'sizes'	=> array(),
				);
				$lastID = $row['sizebreak_id'];
			}
			
			$return[$row['sizebreak_id']]->sizes[$row['size_id']] = $row['string_value'];
		}
	}	

	return $return;

}