<?php

namespace Message\Cog\Field\Type;

use Message\Cog\Field;
use Message\Mothership\CMS\Page\Page;

use Message\Cog\Form\Handler;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

/**
 * A field for a link to an internal or external page.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Link extends Field\MultipleValueField implements ContainerAwareInterface
{
	protected $_services;

	const SCOPE_CMS      = 'cms';
	const SCOPE_EXTERNAL = 'external';
//	const SCOPE_ROUTE    = 'route'; # for a future version?
	const SCOPE_ANY      = 'any';

	public function getFieldType()
	{
		return 'link';
	}

	/**
	 * {@inheritdoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), new Field\FormType\Link, $this->getLabel(), $this->getFieldOptions());
	}

	public function setScope($scope)
	{
		if (!in_array($scope, array(
			self::SCOPE_CMS,
			self::SCOPE_EXTERNAL,
			self::SCOPE_ANY,
		))) {
			throw new \InvalidArgumentException(sprintf('Invalid scope: `%s`', $scope));
		}

		// actually, maybe this makes more sense on the form field object?

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValueKeys()
	{
		return array(
			'scope',
			'target',
		);
	}

	/**
	 * Magic method for converting this object to a string.
	 *
	 * If the scope is "cms", the returned value is the full slug to the target
	 * page (regardless of whether this page has been deleted or not).
	 *
	 * If the scope is "external", the returned value is just the target link.
	 *
	 * @return string The evaluated link target
	 */
	public function __toString()
	{
		if (self::SCOPE_CMS === $this->_value['scope']) {
			$page = $this->_services['cms.page.loader']
				->includeDeleted(true)
				->getByID((int) $this->_value['target']);

			if ($page instanceof Page) {
				return $page->slug->getFull();
			}
		}

		return $this->_value['target'];
	}
}