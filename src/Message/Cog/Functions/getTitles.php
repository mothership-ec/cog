<?php


function getTitles() {
	
	$query = 'SELECT title_id, title_name FROM val_title ORDER BY title_id';
	
	if ($res = new DBquery($query)) {
		
		while($row = $res->row()) {
			$titles[$row['title_id']] = $row['title_name'];
		}
	}
	return $titles;
}



?>