<?php

namespace Message\Cog\Filter;

use Message\Cog\DB\QueryBuilderInterface;

/**
 * Class AbstractFilter
 * @package Message\Cog\Filter
 *
 * @author  Samuel Trangmar-Keates <sam@mothership.ec>
 *
 * Callback based filter. Filters and forms built using callbacks.
 */
class CallbackFilter implements FilterInterface
{
	/**
	 * @var string
	 */
	private $_name;

	/**
	 * @var string
	 */
	private $_displayName;

	/**
	 * @var mixed
	 */
	protected $_value;

	/**
	 * @var array
	 */
	protected $_options = [];

	/**
	 * @var \Callable
	 */
	protected $_filterCallback;

	/**
	 * @var \Callable
	 */
	protected $_formCallback;

	/**
	 * Set the name and display name on instanciation, and set the label of the form field to
	 * the display name
	 *
	 * @param $name
	 * @param $displayName
	 */
	public function __construct($name, $displayName, callable $filterCallback, callable $formCallback = null)
	{
		$this->_setName($name);
		$this->_setDisplayName($displayName);

		$this->_filterCallback = $filterCallback;
		$this->_formCallback = $formCallback;

		$this->_options['label'] = $this->getDisplayName();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDisplayName()
	{
		return $this->_displayName;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Default to 'hidden' field. Use the form callback if set.
	 */
	public function getForm()
	{
		if($this->_formCallback !== null) {
			$call = $this->_formCallback; 

			return $call($this->_options);
		}

		return 'hidden';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setOptions(array $options)
	{
		$this->_options = $options + $this->_options;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setValue($value)
	{
		$this->_value = $value;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Runs the filter callback, passing the queryBuilder and value as parameters.
	 */
	public function apply(QueryBuilderInterface $queryBuilder)
	{
		$call = $this->_filterCallback; 

		return $call($queryBuilder, $this->_value);
	}

	/**
	 * Validate the type for the name and then set it
	 *
	 * @param $name
	 * @throws \InvalidArgumentException   Throws exception if $name is not a string
	 */
	protected function _setName($name)
	{
		if (!is_string($name)) {
			throw new \InvalidArgumentException('First parameter must be a string, ' . gettype($name) . ' given');
		}

		$this->_name = $name;
	}
	/**
	 * Validate the type for the display name and then set it
	 *
	 * @param $displayName
	 * @throws \InvalidArgumentException   Throws exception if $displayName is not a string
	 */
	protected function _setDisplayName($displayName)
	{
		if (!is_string($displayName)) {
			throw new \InvalidArgumentException('Second parameter must be a string, ' . gettype($displayName) . ' given');
		}

		$this->_displayName = $displayName;
	}
}