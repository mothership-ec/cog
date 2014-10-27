<?php

namespace Message\Cog\HTTP\REST;

use Message\Cog\ValueObject\Collection;

class RequestDispatcherCollection extends Collection
{
	public function __construct(array $items = [])
	{
		$this->addValidator(function ($item) {
			if (!$item instanceof RequestDispatcherInterface) {
				throw new \InvalidArgumentException('Expecting instance of RequestDispatcherInterface');
			}
		});

		$this->setKey(function ($item) {
			return $item->getName();
		});

		parent::__construct($items);
	}
}