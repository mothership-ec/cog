<?php

namespace Message\Cog\Console\Task;

use Message\Cog\Console\Command;
use Message\Cog\Service\ContainerAwareInterface;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cron\CronExpression;

/**
 * Task
 *
 * Many of the methods in this class are declared as final as Tasks extend this
 * class and we don't want them to interfere with functionality.
 */
abstract class Task extends Command
{
	protected $_input;
	protected $_cronExpression   = false;
	protected $_cronEnvironments = array();
	protected $_outputHandlers   = array();
	protected $_buffer           = '';

	final public function __construct($name)
	{
		parent::__construct($name);
	}

	final public function addOutputHandler(OutputHandler\OutputHandler $handler)
	{
		if($handler instanceof ContainerAwareInterface) {
			$handler->setContainer($this->_services);
		}

		$handler->setTask($this);
		$this->_outputHandlers[$handler->getName()] = $handler;
	}

	final public function getOutputHandler($handler)
	{
		if (!isset($this->_outputHandlers[$handler])) {
			throw new \RuntimeException(sprintf("Output handler '%s' does not exist on task", $handler));
		}

		return $this->_outputHandlers[$handler];
	}

	final public function schedule($expression, $env = array())
	{
		$env = (array) $env;
		$this->_cronExpression   = CronExpression::factory($expression);
		$this->_cronEnvironments = $env;
	}

	final public function getCronExpression()
	{
		return $this->_cronExpression;
	}

	final public function getCronEnvironments()
	{
		return $this->_cronEnvironments;
	}

	final public function output($name)
	{
		return isset($this->_outputHandlers[$name]) ? $this->_outputHandlers[$name] : false;
	}

	final public function getRawOutput()
	{
		return $this->_output;
	}

	final public function getRawInput()
	{
		return $this->_input;
	}


	final protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->_input  = $input;
		$this->_output = $output;

		$exception = '';
		$returned  = '';

		ob_start(); // capture any calls to `echo` or `print`
		try {
			$returned = $this->process();
		} catch(\Exception $e) {
			$exception = $e->getMessage();
		}
		$printed = ob_get_clean();

		foreach($this->_outputHandlers as $handler) {
			$all = implode("\n", array_filter(array(
				$this->getBuffer(), $printed, $returned, $exception
			)));
			$handler->process(array($all, $this->getBuffer(), $printed, $returned, $exception));
		}
	}

	final public function write($text)
	{
		$this->_buffer .= $text;
		$this->_output->write($text);
	}

	final public function writeln($text)
	{
		$this->write($text."\n");
	}

	final public function getBuffer()
	{
		return $this->_buffer;
	}

	abstract public function process();
}