<?php

function dateUKtoAmerican($date,$replace_separator=FALSE){
	$return=$date;
	$days='0?[1-9]|[12][0-9]|3[01]';
	$months='0?[1-9]|1[0-2]';
	$year='\d{2}|\d{4}';
	$non_alpha='[^0-9a-zA-Z]+';
	$return=preg_replace("/^\s*($days)($non_alpha)($months)($non_alpha)($year)/",$replace_separator===FALSE?'$3$2$1$4$5':'$3'.$replace_separator.'$1'.$replace_separator.'$5',$date);
	return $return;
}