<?php

namespace Message\Cog\Filesystem;

use Message\Cog\ReferenceParser;


class StreamWrapper /* implements StreamWrapperInterface */
{
	protected $_referenceParser;
	protected $_prefix;
	protected $_maps = array();

	public function __construct(ReferenceParser $referenceParser, $prefix = 'cog')
	{
		$this->_referenceParser = $referenceParser;
		$this->_prefix          = $prefix;
	}

	public function register()
	{
		\Message\Cog\Filesystem\StreamWrapperHelper::setNextHandler($this);
		if(!stream_wrapper_register($this->_prefix, '\\Message\\Cog\\Filesystem\\StreamWrapperHelper')) {
			throw new \Exception(sprintf('Could not register stream wrapper `%s://`', $this->_prefix));
		}
	}

	public function unregister()
	{
		if(!stream_wrapper_unregister($this->_prefix)) {
			throw new \Exception(sprintf('Could not unregister stream wrapper `%s://`', $this->_prefix));
		}
	}

	public function restore()
	{
		if(!stream_wrapper_restore($this->_prefix)) {
			throw new \Exception(sprintf('Could not unregister stream wrapper `%s://`', $this->_prefix));
		}
	}

	public function map($from, $to)
	{
		// check the regex is valid
		if (preg_match($from, '') === false) {
			throw new \Exception(sprintf('Invalid regex `%s`', $from));
		}

		$this->_maps[$from] = $to;
	}

	public function stream_open($path, $mode, $options, &$opened_path)
	{
		$url = parse_url($path);
		$this->varname = $url["host"];
		$this->position = 0;

		return true;
	}

	public function stream_read($count)
	{
		$ret = substr($GLOBALS[$this->varname], $this->position, $count);
		$this->position += strlen($ret);
		return $ret;
	}

	public function stream_write($data)
	{
		$left = substr($GLOBALS[$this->varname], 0, $this->position);
		$right = substr($GLOBALS[$this->varname], $this->position + strlen($data));
		$GLOBALS[$this->varname] = $left . $data . $right;
		$this->position += strlen($data);
		return strlen($data);
	}

	public function stream_tell()
	{
		return $this->position;
	}

	public function stream_eof()
	{
		return $this->position >= strlen($GLOBALS[$this->varname]);
	}

	public function stream_seek($offset, $whence)
	{
		switch ($whence) {
			case SEEK_SET:
				if ($offset < strlen($GLOBALS[$this->varname]) && $offset >= 0) {
					 $this->position = $offset;
					 return true;
				} else {
					 return false;
				}
				break;

			case SEEK_CUR:
				if ($offset >= 0) {
					 $this->position += $offset;
					 return true;
				} else {
					 return false;
				}
				break;

			case SEEK_END:
				if (strlen($GLOBALS[$this->varname]) + $offset >= 0) {
					 $this->position = strlen($GLOBALS[$this->varname]) + $offset;
					 return true;
				} else {
					 return false;
				}
				break;

			default:
				return false;
		}
	}

	public function stream_metadata($path, $option, $var) 
	{
		if($option == STREAM_META_TOUCH) {
			$url = parse_url($path);
			$varname = $url["host"];
			if(!isset($GLOBALS[$varname])) {
				$GLOBALS[$varname] = '';
			}
			return true;
		}
		return false;
	}

	public function stream_stat()
	{
		
	}
}