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
