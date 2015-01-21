<?php

namespace Message\Cog\HTTP;

class Methods
{
	static public function get()
	{
		return [
			'OPTIONS',
			'GET',
			'HEAD',
			'POST',
			'PUT',
			'DELETE',
			'TRACE',
			'CONNECT'
		];
	}
}