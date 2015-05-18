<?php

namespace Message\Cog\Filter;

/**
 * Class DataBinder
 * @package Message\Cog\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class DataBinder
{
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