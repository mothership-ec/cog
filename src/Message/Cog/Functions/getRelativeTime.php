<?php

function getRelativeTime($date)
{
	$diff = time() - $date;
	
	# < 1 MINUTE AGO
	if ($diff < 60) {
		return $diff . ' second' . $this->_services['fns.text']->plural($diff) . ' ago';
	}

	# CHANGE UNIT TO MINUTES
	$diff = round($diff / 60);
	
	# < 1 HOUR AGO
	if ($diff < 60) {
		return $diff . ' minute' . $this->_services['fns.text']->plural($diff) . ' ago';
	}

	# CHANGE UNIT TO HOURS
	$diff = round($diff/60);
	
	# < 1 DAY AGO
	if ($diff < 24) {
		return $diff . ' hour' . $this->_services['fns.text']->plural($diff) . ' ago';
	}

	# CHANGE UNIT TO DAYS
	$diff = round($diff / 24);
	
	# < 7 DAYS AGO
	if ($diff < 7) {
		return $diff . ' day' . $this->_services['fns.text']->plural($diff) . ' ago';
	}

	# CHANGE UNIT TO WEEKS
	$diff = round($diff / 7);
	
	# < 4 WEEKS AGO
	if ($diff < 4) {
		return $diff . ' week' . $this->_services['fns.text']->plural($diff) . ' ago';
	}

	return 'on ' . date('F j, Y', $date);
}