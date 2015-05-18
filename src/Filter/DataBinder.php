<?php

namespace Message\Cog\Filter;

/**
 * Class DataBinder
 * @package Message\Cog\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class for binding form data to filters via the `setValue()` method. Will create a new instance of the
 * FilterCollection, to remove any filters not present in the form data
 */
class DataBinder
{
	/**
	 * Binds form data to filters, and then adds those filters to a new instance of FilterCollection,
	 * discarding the original and any filters that do not have values
	 *
	 * @param array $data
	 * @param FilterCollection $filters
	 *
	 * @return FilterCollection
	 */
	public function bindData(array $data, FilterCollection $filters)
	{
		$boundFilters = new FilterCollection;
		foreach ($data as $key => $value) {
			if ($filters->exists($key)) {
				$filter = $filters[$key];
				$filter->setValue($value);
				$boundFilters->add($filter);
			}
		}

		return $boundFilters;
	}
}