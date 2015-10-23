<?php

namespace Message\Cog\AssetManagement;

use Assetic\Filter\FilterInterface;
use Assetic\Asset\AssetInterface;

/**
 * Class NullFilter
 * @package Message\Cog\AssetManagement
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Filter class for bypassing minification
 */
class NullFilter implements FilterInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function filterLoad(AssetInterface $asset)
	{}

	/**
	 * {@inheritDoc}
	 */
	public function filterDump(AssetInterface $asset)
	{}
}