<?php

namespace Message\Cog\Form;

use Symfony\Component\Form\FormRenderer as SymfonyRenderer;
use Symfony\Component\Form\FormView;

/**
 * Class Renderer
 *
 * Adapted from \Symfony\Component\Form\FormRenderer
 *
 * Removed references to cache and replaced calls to private $engine property with getEngine() method
 *
 * @package Message\Cog\Form
 */
class Renderer extends SymfonyRenderer
{
	public function renderBlock(FormView $view, $blockName, array $variables = array())
	{
		$resource = $this->getEngine()->getResourceForBlockName($view, $blockName);

		if (!$resource) {
			throw new \Exception(sprintf('No block "%s" found while rendering the form.', $blockName));
		}

		// Merge the passed with the existing attributes
		if (isset($variables['attr']) && isset($scopeVariables['attr'])) {
			$variables['attr'] = array_replace($scopeVariables['attr'], $variables['attr']);
		}

		// Merge the passed with the exist *label* attributes
		if (isset($variables['label_attr']) && isset($scopeVariables['label_attr'])) {
			$variables['label_attr'] = array_replace($scopeVariables['label_attr'], $variables['label_attr']);
		}

		// Do not use array_replace_recursive(), otherwise array variables
		// cannot be overwritten
		$variables = array_replace($scopeVariables, $variables);

		// Do the rendering
		$html = $this->getEngine()->renderBlock($view, $resource, $blockName, $variables);

		return $html;
	}
}