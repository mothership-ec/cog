<?php

namespace Message\Cog\Mail;

use Exception;

class Factory implements MailableInterface {

	protected $_message;
	protected $_requires;
	protected $_items;
	protected $_callbacks = array();

	/**
	 * Constructor, sets the message to build and dispatch.
	 *
	 * @param Message $message
	 */
	public function __construct(Message $message)
	{
		$this->_message = $message;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMessage()
	{
		if ($diff = array_diff_key(array_flip($this->_requires), $this->_items) and count($diff)) {
			throw new Exception(sprintf("Required key(s) not set on factory: %s", implode(', ', array_keys($diff))));
		}

		foreach ($this->_callbacks as $priority => $fns) {
			foreach ($fns as $fn) {
				$fn($this, $this->_message);
			}
		}

		return $this->_message;
	}

	/**
	 * Extend the factory with callbacks, can be used to alter the message
	 * through the service container.
	 *
	 * @param  Closure  $callback
	 * @param  integer  $priority Optional priority, low to high
	 * @return Factory
	 */
	public function extend(\Closure $callback, $priority = 0)
	{
		$this->_callbacks[$priority][] = $callback;

		return $this;
	}

	/**
	 * Set items required for building the message.
	 *
	 * @return void
	 */
	public function requires(/*...*/)
	{
		$keys = func_get_args();
		if (count($keys) == 1 and is_array($keys[0])) {
			$keys = $keys[0];
		}

		$this->_requires = $keys;
	}

	/**
	 * Set a required item.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return Factory
	 */
	public function set($key, $value)
	{
		if (! in_array($key, $this->_requires)) {
			throw new Exception(sprint("Can not set key that is not required: '%s'", $key));
		}

		$this->_items[$key] = $value;

		return $this;
	}

	/**
	 * Get an item from the required items.
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if (isset($this->_items[$key])) {
			return $this->_items[$key];
		}

		throw new Exception(sprintf("No item found with key '%s'", $key));
	}

}