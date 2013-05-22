<?php

namespace Message\Cog\Form;

use Symfony\Component\Form\Form as SymfonyForm;

class Form extends SymfonyForm
{

	public function __toString()
	{
		try {
			$elements = $this->all();
			$return = '';

			var_dump($elements);

			foreach ($elements as $element) {
				$return .= $element;
			}

			return $return;
		}
		catch (\Exception $e) {
			var_dump($e);
			exit;
		}
	}

}