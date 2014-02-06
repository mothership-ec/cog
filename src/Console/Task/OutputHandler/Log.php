<?php

namespace Message\Cog\Console\Task\OutputHandler;

use Psr\Log\LoggerInterface;
use Monolog\Handler\HandlerInterface;

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
	 * Get the logger instance.
	 *
	 * @return LoggerInterface
	 */
	public function getLogger()
	{
		return $this->_logger;
	}

	public function setLogHandler(HandlerInterface $handler)
	{
		$this->_logger->popHandler();
		$this->_logger->pushHandler($handler);
	}

	/**
	 * {inheritDoc}
	 */
	public function process(array $args)
	{
		if(!$this->_output) {
			return false;
		}

		// Get the first argument as the output
		$output = array_shift($args);

		// Log the output with any remaining arguments sent as the context
		$this->_logger->info($output, $args);
	}
}
