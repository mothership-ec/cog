<?php

use \Mothership\Framework\Services;

//SAFE WAY TO START SESSIONS - 
//WILL NOT CALL SESSION_START IF A SESSION IS ALREADY ACTIVE
function startSession()
{
	if (!session_id()) {
		if(Services::get('environment')->isLocal()) {
			session_name(Services::get('environment')->get().'_'.$_SERVER['SERVER_PORT']);
		}
		session_start();
	}
	return session_id();
}