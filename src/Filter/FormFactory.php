<?php

namespace Message\Cog\Filter;

/**
 * Class FormBuilder
 * @package Message\Cog\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class FormFactory
{
	const NAME = 'filter_form';

	/**
	 * @param FilterCollection $filters
	 * @param null $name
	 *
	 * @return FilterForm
	 */
	public function getForm(FilterCollection $filters, $name = null)
	{
		if (!count($filters)) {
			throw new Exception\NoFiltersException('No filters to create form with!');
		}

		$name = $name ?: self::NAME;

		return new FilterForm($name, $filters);
	}
}