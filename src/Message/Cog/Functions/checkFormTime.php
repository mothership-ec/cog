<?php


//CHECK FOR A SUBMITTED FORM AND CHECK TO SEE IF ITS BEEN SUBMITTED BEFORE
function checkFormTime() {
	if (isset($_POST['ftime'])) {
		//CHECK THAT THE FORM HAS NOT BEEN SUBMITTED BEFORE
		if (!isset($_SESSION['ftime']) || (isset($_SESSION['ftime']) && $_SESSION['ftime'] != $_POST['ftime'])) {
			//SAVE THE FORM TIME
			$_SESSION['ftime'] = $_POST['ftime'];
			return true;
		}
	}
	return false;
}


?>