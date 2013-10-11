<?php

namespace Message\Cog\Pagination\Adapter;

interface AdapterInterface {

	public function getCount();
	public function getSlice($offset, $length);

}