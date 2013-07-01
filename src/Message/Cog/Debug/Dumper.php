<?php

namespace Message\Cog\Debug;

/**
 * Variable dumper, useful for developers debugging Cog and Cog applications.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Dumper
{
	const DEPTH_VAR  = 'xdebug.var_display_max_depth';
	const LENGTH_VAR = 'xdebug.var_display_max_data';

	protected $_variables;

	protected $_callFile;
	protected $_callLine;

	protected $_quit = false;

	protected $_origDepth;
	protected $_depth;

	protected $_origLength;
	protected $_length;

	protected $_backtraceOffset;

	/**
	 * Constructor.
	 *
	 * This allows the calling code to set an "offset" to use when determining
	 * where in the `debug_backtrace()` the actual call can be found. This is
	 * useful for when this class is wrapped in functions to make calling it
	 * easier.
	 *
	 * @param integer $backtraceOffset Offset to use when finding the call
	 */
	public function __construct($backtraceOffset = 0)
	{
		$this->_backtraceOffset = $backtraceOffset;
	}

	/**
	 * Destructor.
	 *
	 * Applies any configured settings such as the maximum string length and
	 * maximum array depth when using `var_dump()`.
	 *
	 * Each of the variables set is then dumped using `var_dump()`, and the
	 * calling file and line are output.
	 *
	 * Any settings are then reverted back to their original values.
	 *
	 * If this object has been configured to exit after dumping, `exit` is
	 * then called.
	 */
	public function __destruct()
	{
		$this->_applySettings();

		foreach ($this->_variables as $var) {
			var_dump($var);
		}

		echo sprintf('<p>Dump in <strong>%s</strong>:<strong>%s</strong></p>',
			$this->_callFile,
			$this->_callLine
		);

		$this->_revertSettings();

		if ($this->_quit) {
			exit;
		}
	}

	/**
	 * Invoke magic method, proxies the call to `dump()`.
	 *
	 * @see dump
	 *
	 * @param mixed $var,... Unlimited number of variables to dump
	 *
	 * @return Dumper        Returns $this for chainability
	 */
	public function __invoke()
	{
		$this->_snapshotCall();

		return call_user_func_array(array($this, 'dump'), func_get_args());
	}

	/**
	 * Set the variables to be dumped.
	 *
	 * @param mixed $var,... Unlimited number of variables to dump
	 *
	 * @return Dumper        Returns $this for chainability
	 */
	public function dump()
	{
		$this->_snapshotCall();

		$this->_variables = func_get_args();

		return $this;
	}

	/**
	 * Set whether this dumper should exit the script once the variables are
	 * dumped.
	 *
	 * @param  boolean $bool True to exit, false to not
	 *
	 * @return Dumper        Returns $this for chainability
	 */
	public function quit($bool = true)
	{
		$this->_quit = (boolean) $bool;

		return $this;
	}

	/**
	 * Set the maximum depth to show when outputting arrays. E.g. 1 would only
	 * output a single-dimension array.
	 *
	 * If a value larger than 1023 is passed, 1023 is used, as this is the
	 * maximum value for this setting.
	 *
	 * @param  int $depth Maximum array depth
	 *
	 * @return Dumper     Returns $this for chainability
	 */
	public function depth($depth)
	{
		$this->_origDepth = ini_get(self::DEPTH_VAR);
		$this->_depth     = (int) $depth > 1023 ? 1023 : $depth;

		return $this;
	}

	/**
	 * Set the maximum number of characters to show when outputting a string.
	 *
	 * If the argument is left blank or passed as -1, the limit is turned off
	 * and strings are output regardless of their length.
	 *
	 * @param  int $length Maximum string length
	 *
	 * @return Dumper      Returns $this for chainability
	 */
	public function length($length = -1)
	{
		$this->_origlength = ini_get(self::LENGTH_VAR);
		$this->_length     = (int) $length;
	}

	/**
	 * Snapshot where the dumper was called from, for use when dumping the
	 * variables.
	 */
	protected function _snapshotCall()
	{
		if ($this->_callFile) {
			return;
		}

		$backtrace = debug_backtrace();
		$last      = $backtrace[($this->_backtraceOffset + 1)];

		$this->_callFile  = $last['file'];
		$this->_callLine  = $last['line'];
	}

	/**
	 * Apply depth & length settings, if defined.
	 */
	protected function _applySettings()
	{
		if ($this->_depth) {
			ini_set(self::DEPTH_VAR, $this->_depth);
		}

		if ($this->_length) {
			ini_set(self::LENGTH_VAR, $this->_length);
		}
	}

	/**
	 * Revert depth & length settings, if they were changed.
	 */
	protected function _revertSettings()
	{
		if ($this->_origDepth) {
			ini_set(self::DEPTH_VAR, $this->_origDepth);
		}

		if ($this->_origLength) {
			ini_set(self::LENGTH_VAR, $this->_origLength);
		}
	}
}