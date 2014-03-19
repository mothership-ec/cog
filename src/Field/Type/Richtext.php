<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field\Field;
use Message\Cog\Form\Handler;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Symfony\Component\Form\FormBuilder;


/**
 * A field for text written in a rich text markup language.
 *
 * Note this class is named "Richtext" not "RichText", this is important because
 * otherwise this field won't work properly on case-sensitive systems.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Richtext extends Field implements ContainerAwareInterface
{
	protected $_services;
	protected $_engine  = 'markdown';
	protected $_engines = array(
		'markdown',
	);

	public function __toString()
	{
		if ('markdown' === $this->_engine) {
			return $this->_services['markdown.parser']->transformMarkdown($this->_value);
		}

		return parent::__toString();
	}

	public function getFieldType()
	{
		return 'richtext';
	}

	/**
	 * {@inheritdoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'textarea', $this->getFieldOptions());
	}

	/**
	 * Set the rendering engine to use.
	 *
	 * @param string $engine Identifier for the rendering engine
	 *
	 * @return RichText      Returns $this for chainability
	 *
	 * @throws \InvalidArgumentException If the engine is not recognised
	 */
	public function setEngine($engine)
	{
		$engine = strtolower($engine);

		if (!in_array($engine, $this->_engines)) {
			throw new \InvalidArgumentException(sprintf('Rich text engine `%s` does not exist.', $engine));
		}

		$this->_engine = $engine;

		return $this;
	}
}