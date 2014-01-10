<?php

namespace Message\Cog\Console\Command\Event;

use Message\Cog\Event\Event;

use Symfony\Component\Console\Output\OutputInterface;

class Status extends Event
{
	const LINE_WIDTH = 50;

	protected $_checks = 0;
	protected $_passed = 0;
	protected $_failed = 0;

	public function __construct(OutputInterface $output)
	{
		$this->_output = $output;
	}

	/**
	 * Run a status check and output the result.
	 *
	 * @param  string   $title The name for this check
	 * @param  \Closure $func  A callback that must return true or false, or throw and exception.
	 *
	 * @return Status Returns instance of itself for chainability
	 */
	public function check($title, \Closure $func)
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

		return $this;
	}

	/**
	 * Check to see if a directory or file exists and is writeable.
	 *
	 * @param  string $title The name of the path.
	 * @param  string $path  The file path
	 *
	 * @return Status Returns instance of itself for chainability
	 */
	public function checkPath($title, $path)
	{
		$this->report($title . ' location', $path);
		$this->check($title . ' is created', function() use ($path) { 
			return file_exists($path) && is_dir($path);
		});
		$this->check($title . ' is writeable', function() use ($path) {
			return is_writeable($path);
		});

		return $this;
	}

	/**
	 * Output a variable during a status check. Useful for reporting literal values.
	 *
	 * @param  string $title The title of the variable
	 * @param  mixed $value The value of the variable
	 *
	 * @return Status Returns instance of itself for chainability
	 */
	public function report($title, $value)
	{
		$this->_output->writeln(str_pad($title, self::LINE_WIDTH, '.') . '<info>'.$value.'</info>');

		return $this;
	}

	/**
	 * Renders a bold heading in the output.
	 *
	 * @param  string $heading The heading to putput
	 *
	 * @return Status Returns instance of itself for chainability
	 */
	public function header($heading)
	{
		$this->_output->writeln('<bold>'.$heading.'</bold>');

		return $this;
	}

	/**
	 * Prints a summary of all checks made
	 *
	 * @return Status Returns instance of itself for chainability
	 */
	public function summerise()
	{
		$this->header('Summary');

		$this->_output->writeln('Total checks:   '.$this->_checks);
		$this->_output->writeln('Passed:         '.$this->_passed);
		$this->_output->writeln('Failed:         '.$this->_failed);

		return $this;
	}
}