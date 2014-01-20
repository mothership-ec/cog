<?php

namespace Message\Cog\Console\Task\OutputHandler;

use Psr\Log\LoggerInterface;

class Log extends OutputHandler
{
	protected $_logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->_logger = $logger;
	}

	/**
	 * {inheritDoc}
	 */
	public function getName()
	{
		return 'log';
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

		$this->_logger->log();

		if(!$this->_append && is_writable(dirname($this->_path))) {
			file_put_contents($this->_path, $output);
		} else if($this->_append && is_writable($this->_path)) {
			file_put_contents($this->_path, $output, FILE_APPEND);
		} else {
			$this->_task->getRawOutput()->writeln('<error>Cannot write to '.$this->_path.'</error>');
		}
	}
}
