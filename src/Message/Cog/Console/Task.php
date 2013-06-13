<?php

namespace Message\Cog\Console;

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
	protected $_services;
	protected $_input;
	protected $_cronExpression = false;
	protected $_outputHandlers = array();
	protected $_buffer = '';

	final public function __construct($name)
	{
		parent::__construct($name);

		// output to console by default
		$this->printOutput(true);
	}

	final public function registerHandler($name, $handler)
	{
		$this->_outputHandlers[$name] = $handler;
	}

	final public function mailOutput($recipients, $subject = false, $body = false, $filename = false)
	{
		// if a single string is provided turn it into the format we expect
		if(!is_array($recipients)) {
			$recipients = array($recipients => '');
		}

		$this->registerHandler('mail', function($buffer, $printed, $returned) 
				use ($recipients, $subject, $body, $filename) {
			$output = $buffer.$printed.$returned;
			// TODO: Use Cog's proper email component when it's done
			$subject = $subject ?: 'Output of '.$this->getName();
			$body    = $body ?: !$filename ? $output : '';
			mail(implode(', ', $recipients), $subject, $body);
		});

		// TODO: return the email component object so the developer can modify it if they wish
	}

	final public function saveOutput($path, $append = false)
	{
		$this->registerHandler('file', function($output) use ($path, $append) {
			$output = $buffer.$printed.$returned;
			if(!$append && is_writable(dirname($path))) {
				file_put_contents($path, $output);
			} else if($append && is_writable($path)) {
				file_put_contents($path, $output, FILE_APPEND);
			} else {
				$this->_output->writeln('<error>Cannot write to '.$path.'</error>');
			}
		});
	}

	final public function printOutput($write = true)
	{
		$self = $this;
		$this->registerHandler('print', function($buffer, $printed, $returned) use ($write, $self) {
			$output = $buffer.$printed.$returned;
			if($write) {
				$self->getOutput()->writeln($output);
			}
		});
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

	final public function isDue($time, $env)
	{
		if(!$this->_cronExpression) {
			return false;
		}
		if(!$this->_cronExpression->isDue($time)) {
			return false;
		}
		if(count($this->_cronEnvironments) && !in_array($env, $this->_cronEnvironments)) {
			return false;
		}

		return true;
	}

	final public function getOutputHandler($type)
	{
		return isset($this->_outputHandlers[$type]) ? $this->_outputHandlers[$type] : false;
	}

	final public function getOutput()
	{
		return $this->_output;
	}

	final protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->_input  = $input;
		$this->_output = $output;

		ob_start(); // capcture any calls to `echo`
		$returnedOutput = $this->process();
		$printedOutput = ob_get_clean();

		foreach($this->_outputHandlers as $handler) {
			call_user_func($handler, $this->getBuffer(), $printedOutput, $returnedOutput);
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