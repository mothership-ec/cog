<?php

namespace Message\Cog\Test\Filter;

use Message\Cog\Filter\AbstractFilter;
use Message\Cog\DB\QueryBuilderInterface;

/**
 * Class FauxFilter
 * @package Message\Cog\Test\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class that extends the AbstractFilter for testing the base methods
 */
class FauxFilter extends AbstractFilter
{
	protected function _applyFilter(QueryBuilderInterface $queryBuilder)
	{

	}
}