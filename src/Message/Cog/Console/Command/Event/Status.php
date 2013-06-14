<?php

namespace Message\Cog\Console\Command\Event;

use Message\Cog\Event\Event;

class Status extends Event
{
	const LINE_WIDTH = 100;

	protected $_checks = 0;
	protected $_passed = 0;
	protected $_failed = 0;

	public function __construct($output)
	{
		$this->_output = $output;
	}

	public function check($title, $func)
	{
		$this->_checks++;
		$this->_output->write(str_pad($title, self::LINE_WIDTH, '.'));

		try {
			$result = call_user_func($func);

			if($result) {
				$this->_passed++;
			} else {
				$this->_failed++;
			}

			$result = $result ? '<info>PASSED</info>' : '<error>FAILED</error>';
		} catch(\Exception $e) {
			$this->_failed++;
			$result = '<error>ERROR</error>'."\n";
			$result.= '    <bold>'.$e->getMessage().'</bold>';
		}

		$this->_output->writeln($result);
	}

	public function checkDirectory($title, $path)
	{
		$this->report($title . ' location', $path);
		$this->check($title . ' is created', function() use ($path) { 
			return file_exists($path) && is_dir($path);
		});
		$this->check($title . ' is writeable', function() use ($path) {
			return is_writeable($path);
		});
	}

	public function report($title, $value)
	{
		$this->_output->writeln(str_pad($title, self::LINE_WIDTH, '.') . '<info>'.$value.'</info>');
	}

	public function header($heading)
	{
		$this->_output->writeln('<bold>'.$heading.'</bold>');
	}

	public function summerise()
	{
		$this->header('Summary');

		$this->_output->writeln('Total checks:   '.$this->_checks);
		$this->_output->writeln('Passed:         '.$this->_passed);
		$this->_output->writeln('Failed:         '.$this->_failed);
	}
}