<?php

namespace Message\Cog\Debug;

use Closure;

/**
 * Profiler
 *
 * Snapshots execution time, memory and query usage during page generation.
 *
 * @author James Moss <james@message.co.uk>
 */
class Profiler
{
	protected $_name           = null;
	protected $_snapshots      = array();
	protected $_relative       = true;
	protected $_initialTime    = 0;
	protected $_queryCountFunc = null;

	/**
	 * Constructor
	 *
	 * @param string  $name     The name of this profiler session. Used in output display.
	 * @param boolean $relative Indicts if timing should start from beginning of the
	 *                          page request or just the initialisation of this class
	 */
	public function __construct($name = 'default', Closure $queryCountFunc = null, $relative = true)
	{
		$this->_name        = $name;
		$this->_relative    = $relative;
		$this->_initialTime = microtime(true);
		$this->_queryCountFunc = $queryCountFunc;

		if(!$this->_relative) {
			$metrics = $this->_getSnapshot();
			foreach($metrics as &$metric) {
				$metric = 0;
			}
			// REQUEST_TIME_FLOAT is only available in php 5.4
			if(isset($_SERVER['REQUEST_TIME_FLOAT'])) {
				$this->_initialTime = $_SERVER['REQUEST_TIME_FLOAT'];
			}
		} else {
			$metrics = array();
		}

		$this->poll('Starting profiler', $metrics);
	}

	/**
	 * Stores a snapshot of resource usage at the current point in time
	 *
	 * @param  string $label Labels key points of execution. Used in output display.
	 * @return Profiler
	 */
	public function poll($label = null, array $metrics = array())
	{
		$this->_snapshots[] = array_merge(array('label' => $label), $this->_getSnapshot(), $metrics);

		return $this;
	}

	/**
	 * Internal method which aggregates the different metrics we can track
	 *
	 * @return array Each key in the array is a trackable metric
	 */
	public function _getSnapshot()
	{
		return array(
			'execution_time'	=> $this->getExecutionTime(),
			'memory_usage'		=> $this->getMemoryUsage(),
			'memory_usage_peak'	=> $this->getMemoryUsage(true),
			'num_queries'		=> $this->getNumberOfQueries(),
		);
	}

	/**
	 * Get the number of seconds that the profiler has been running for so far.
	 *
	 * @return float Number of seconds with decimal places
	 */
	public function getExecutionTime()
	{
		return microtime(true) - $this->_initialTime;
	}

	/**
	 * Returns number of database queries performed up to the current point in time
	 *
	 * @return int
	 */
	public function getNumberOfQueries()
	{
		return $this->_queryCountFunc ? call_user_func($this->_queryCountFunc) : 0;
	}

	/**
	 * Gets either the peak or current memory usage.
	 * @param  boolean $peak Set to true to return peak usage.
	 * @return int Memory usage in bytes.
	 */
	public function getMemoryUsage($peak = false)
	{
		return $peak ? memory_get_peak_usage(true) : memory_get_usage(true);
	}

	/**
	 * Return all the snapshots made so far.
	 *
	 * @return array An array of snapshots
	 */
	public function getSnapshots()
	{
		return $this->_snapshots;
	}

	/**
	 * Generates HTML output of this profiler.
	 *
	 * @return string HTML to be added to the page. The profiler will sit in the top right corner.
	 */
	public function renderHtml()
	{
		$this->poll('Rendering profiler');

		// compare the first and last snapshots
		$first = reset($this->_snapshots);
		$last  = end($this->_snapshots);

		// Aggregate our numbers
		$time    = round(($last['execution_time'] - $first['execution_time']) * 1000);
		$memory  = number_format(($last['memory_usage_peak'] - $first['memory_usage_peak']) / 1024 / 1024, 2);
		$queries = $last['num_queries'] - $first['num_queries'];

		// A unique id for this HTML
		$id = 'profiler-'.mt_rand(0, 9999999);

		return '
			<style>
				#'.$id.' { position: fixed; z-index: 100000; bottom: 0; right: 0; font-family: Helvetica Neue, sans-serif; list-style: none; margin: 0; padding: 0; box-shadow: 0 1px 2px rgba(0,0,0,.2); border-radius: 5px 0 0 0; -webkit-transition: opacity .2s; transition: opacity .2s; }
				#'.$id.'.a { opacity: .2; }
				#'.$id.' li { float: left; margin: 0; font-size: 24px; padding: 8px 16px 16px; text-shadow: 0 1px 1px rgba(0,0,0,.3); box-shadow: inset 0 -1px 1px rgba(0,0,0,.1); color: white; text-align: left; }
				#'.$id.' li:first-child { border-radius: 5px 0 0 0;}
				#'.$id.' span { display: block; font-size: 12px; opacity: .75; }
			</style>
			<ul id="'.$id.'" onclick="this.className = (this.className != \'a\' ? \'a\' : \'\');">
				<li style="background:#'.$this->_getRenderColour($time, 200).';"><span>Time (ms)</span>'.$time.'</li>
				<li style="background:#'.$this->_getRenderColour($memory, 5).'"><span>Memory (mb)</span>'.$memory.'</li>
				<li style="background:#'.$this->_getRenderColour($queries, 50).'"><span>Queries</span>'.$queries.'</li>
			</ul>
		';
	}

	/**
	 * Determines which colour the metric should be rendered when displaying.
	 * Green is good, red is bad.
	 *
	 * @param  int $num   The actual value of the metric
	 * @param  int $ideal A target value that $num should be less than.
	 * @return string A hex code colour representing the severity of the metric.
	 */
	protected function _getRenderColour($num, $ideal)
	{
		$colours = array('4D8963', '69A583', 'E1B378', 'E0CC97', 'EC799A', '9F0251');
		$max = $ideal * count($colours);
		$percent = floor($num / $max * count($colours));

		if($percent >= count($colours)) {
			$percent = count($colours) - 1;
		}

		return $colours[$percent];
	}
}