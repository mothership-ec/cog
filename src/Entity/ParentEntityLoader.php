<?php

class ParentEntityLoader
{
	public function __construct(ParentEntityFactoryInterface $entityFactory)
	{
		$this->_entityFactory = $entityFactory;
	}

	public function getByID($id)
	{
		// imagine a db query

		foreach ($results as $row) {
			$entity = $this->_entityFactory->create();


		}
	}
}