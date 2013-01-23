<?php

namespace Message\Cog\Config;

interface LoaderInterface
{
	public function load(Registry $registry);
}