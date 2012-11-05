<?php

function recoverLogin() {
	
	if(isset($_SESSION['details'])) return false;
	
	if(isset($_COOKIE['user'])) {
		
		$parts = explode(':', $_COOKIE['user']);
		
		$user = getUserDetails((int) $parts[0]);
		
		if($parts[1] == md5($user['email_name'].'|'.$user['password'])) {
			$_SESSION['details'] = $user;
			if(isset($_SESSION['basket'])) $_SESSION['basket']->crossSell->refresh();
			return true;
		}
		else {
			setcookie('user', '', time()-3400, '/', '.'.preg_replace('/:([0-9]+)$/', '', str_replace('www.', '', $_SERVER['HTTP_HOST'])), false, true);
			return false;
		}
		
	}
	
}