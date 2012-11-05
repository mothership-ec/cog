<?php

/**
 * This regex comes from here: http://www.regexguru.com/2008/11/detecting-urls-in-a-block-of-text/
 **/

function makeLinks($string) {
	return preg_replace('/(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/ix', '<a href="$0">$0</a>', $string);
}