<?php

namespace Message\Cog\Form\Factory;

use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\FormFactoryBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\FormTypeGuesserChain;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\FormExtensionInterface;
use Symfony\Component\Form\FormTypeExtensionInterface;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Form\ResolvedFormTypeFactoryInterface;
use Message\Cog\Form\Registry;

/**
 * Class FormFactoryBuilder
 * @package Message\Cog\Form
 *
 * Replica of Symfony\Component\Form\FormFactoryBuilder - needed to be replicated to gain access to  private properties.
 * Also adds CoreExtension by default so the Symfony\Component\Form\Forms class is no longer necessary.
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Builder implements FormFactoryBuilderInterface
{
	/**
	 * @var ResolvedFormTypeFactoryInterface
	 */
	protected $_resolvedTypeFactory;

	/**
	 * @var array
	 */
	protected $_extensions = array();

	/**
	 * @var array
	 */
	protected $_types = array();

	/**
	 * @var array
	 */
	protected $_typeExtensions = array();

	/**
	 * @var array
	 */
	protected $_typeGuessers = array();

	public function __construct()
	{
		$this->addExtension(new CoreExtension);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setResolvedTypeFactory(ResolvedFormTypeFactoryInterface $resolvedTypeFactory)
	{
		$this->_resolvedTypeFactory = $resolvedTypeFactory;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addExtension(FormExtensionInterface $extension)
	{
		$this->_extensions[] = $extension;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addExtensions(array $extensions)
	{
		$this->_extensions = array_merge($this->_extensions, $extensions);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addType(FormTypeInterface $type)
	{
		$this->_types[$type->getName()] = $type;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addTypes(array $types)
	{
		foreach ($types as $type) {
			$this->_types[$type->getName()] = $type;
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addTypeExtension(FormTypeExtensionInterface $typeExtension)
	{
		$this->_typeExtensions[$typeExtension->getExtendedType()][] = $typeExtension;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addTypeExtensions(array $typeExtensions)
	{
		foreach ($typeExtensions as $typeExtension) {
			$this->_typeExtensions[$typeExtension->getExtendedType()][] = $typeExtension;
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addTypeGuesser(FormTypeGuesserInterface $typeGuesser)
	{
		$this->_typeGuessers[] = $typeGuesser;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addTypeGuessers(array $typeGuessers)
	{
		$this->_typeGuessers = array_merge($this->_typeGuessers, $typeGuessers);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFormFactory()
	{
		$extensions = $this->_extensions;

		if (count($this->_types) > 0 || count($this->_typeExtensions) > 0 || count($this->_typeGuessers) > 0) {
			if (count($this->_typeGuessers) > 1) {
				$typeGuesser = new FormTypeGuesserChain($this->_typeGuessers);
			} else {
				$typeGuesser = isset($this->_typeGuessers[0]) ? $this->_typeGuessers[0] : null;
			}

			$extensions[] = new PreloadedExtension($this->_types, $this->_typeExtensions, $typeGuesser);
		}

		$resolvedTypeFactory = $this->_resolvedTypeFactory ?: new ResolvedFormTypeFactory();
		$registry = new Registry($extensions, $resolvedTypeFactory);

		return new Factory($registry, $resolvedTypeFactory);
	}
}