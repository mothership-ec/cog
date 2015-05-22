<?php

namespace Message\Cog\Filter;

use Message\Cog\ValueObject\Collection;

/**
 * Class FilterCollection
 * @package Message\Cog\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class FilterCollection extends Collection
{
	/**
	 * {@inheritDoc}
	 */
	protected function _configure()
	{
		$this->addValidator(function ($item) {
			if (!$item instanceof FilterInterface) {
				$type = gettype($item) === 'object' ? get_class($item) : gettype($item);
				throw new \InvalidArgumentException('Item must be a FilterInterface, ' . $type . ' given');
			}
		});

		$this->setKey(function ($item) {
			return $item->getName();
		});
	}
}