<?php

namespace Message\Cog\Console\Task\OutputHandler;


class Log extends OutputHandler
{
	/**
	 * {inheritDoc}
	 */
	public function getName()
	{
		return 'log';
	}

	/**
	 * Enables this output handler
	 *
	 * @param  string  $path   The path to write output to
	 * @param  boolean $append True to append the contents to the file rather than overwrite it
	 *
	 * @return void
	 */
	public function enable($path, $append = false)
	{
		$this->_path   = $path;
		$this->_append = $append;

		parent::enable();
	}

	/**
	 * {inheritDoc}
	 */
	public function process(array $args)
	{
		if(!$this->_output) {
			return false;
		}

		$output = $args[0];

		if(!$this->_append && is_writable(dirname($this->_path))) {
			file_put_contents($this->_path, $output);
		} else if($this->_append && is_writable($this->_path)) {
			file_put_contents($this->_path, $output, FILE_APPEND);
		} else {
			$this->_task->getRawOutput()->writeln('<error>Cannot write to '.$this->_path.'</error>');
		}
	}
}
