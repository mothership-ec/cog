<?php

namespace Message\Cog\AssetManagement;

use Assetic\Filter\FilterInterface;
use Assetic\Asset\AssetInterface;

class NullFilter implements FilterInterface
{
	public function filterLoad(AssetInterface $asset)
	{}

	public function filterDump(AssetInterface $asset)
	{}
}