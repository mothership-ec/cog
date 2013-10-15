<?php

namespace Message\Cog\Pagination\Adapter;

interface AdapterInterface {

	/**
	 * Get the count of results.
	 *
	 * @return int
	 */
	public function getCount();

	/**
	 * Get a slice of the results.
	 *
	 * @param  int $offset Offset at which to begin the slice.
	 * @param  int $length Length of the slice.
	 * @return array       Sliced results.
	 */
	public function getSlice($offset, $length);

}