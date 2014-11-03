<?php

namespace Message\Cog\HTTP\OAuth;

use Message\Cog\ValueObject\Collection;

class TokenCollection extends Collection
{
	public function __construct($items = [])
	{
		$this->addValidator(function ($item) {
			if (!$item instanceof Token) {
				throw new \InvalidArgumentException('Item must be an instance of ' . __NAMESPACE__ . '\\Token');
			}
		});
		$this->setKey(function ($item) {
			return $item->getType();
		});

		parent::__construct($items);
	}
}