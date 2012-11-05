<?php

function apacheDateToUnix($date) { 
	list($D, $M, $d, $h, $m, $s, $z, $Y) = sscanf($date, "%3s %3s %2d %2d:%2d:%2d %5s %4d");
	return strtotime("$d $M $Y $h:$m:$s $z"); 
} 
