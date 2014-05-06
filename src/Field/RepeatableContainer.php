<?php

namespace Message\Cog\Field;

/**
 * Wrapper for Group instances where the groups are repeatable.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class RepeatableContainer implements \IteratorAggregate, \Countable, FieldContentInterface
{
	protected $_group;
	protected $_groups = array();

	/**
	 * Constructor.
	 *
	 * @param Group $group The repeatable group
	 */
	public function __construct(Group $group)
	{
		$this->_group 	  = $group;
	}

	/**
	 * Magic call method, proxies unrecognised method calls to the `Group`
	 * instance stored in this `RepeatableContainer`.
	 *
	 * @param  string $method Method name
	 * @param  array  $args   Array of arguments
	 *
	 * @return mixed          Value returned by calling the method on `Group`
	 *
	 * @throws \BadMethodCallException If the method doesn't exist on `Group`
	 */
	public function __call($method, array $args = array())
	{
		if (!method_exists($this->_group, $method)) {
			throw new \BadMethodCallException(sprintf(
				'Bad method call to `%s:%s`',
				get_class($this),
				$method
			));
		}

		return call_user_func_array(array($this->_group, $method), $args);
	}

	/**
	 * Add a clone of the group to this repeatable set
	 */
	public function add()
	{
		$this->_groups[] = clone $this->_group;
	}

	public function clear()
	{
		$this->_groups = array();
	}

	public function all()
	{
		return $this->_groups;
	}

	/**
	 * Get the number of groups in this repeatable set.
	 *
	 * @return int Number of groups
	 */
	public function count()
	{
		return count($this->_groups);
	}

	/**
	 * Get a group at a specific index from this container.
	 *
	 * @param  int $index  The index
	 *
	 * @return Group|false The group instance, or false if it doesn't exist
	 */
	public function get($index)
	{
		$index = (int) $index;

		return isset($this->_groups[$index]) ? $this->_groups[$index] : false;
	}

	/**
	 * Get the iterator to use for this iterable class.
	 *
	 * @return \ArrayIterator The iterator to use
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_groups);
	}

	/**
	 * {@inheritDoc}
	 * @throws \InvalidArgumentException        Throws exception if a group does not implment
	 *                                          FieldContentInterface
	 */
	public function hasContent()
	{
		$hasContent = false;

		foreach ($this->_groups as $group) {
			if (!$group instanceof FieldContentInterface) {
				throw new \InvalidArgumentException('Group must implement FieldContentInterface');
			}
			$hasContent = ($group->hasContent()) ? true : $hasContent;
		}

		return $hasContent;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getType()
	{
		return __CLASS__;
	}
}