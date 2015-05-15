<?php

namespace Message\Cog\Test\Filter;

use Message\Cog\Filter\AbstractFilter;
use Message\Cog\DB\QueryBuilderInterface;

class FauxFilter extends AbstractFilter
{
	protected function _applyFilter(QueryBuilderInterface $queryBuilder)
	{

	}
}