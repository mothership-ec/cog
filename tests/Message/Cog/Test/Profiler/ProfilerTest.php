<?php

namespace Message\Cog\Test\Profiler;

use ReflectionClass;
use Message\Cog\Profiler\Profiler;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
	public function testSnapshots()
	{
		$profiler = new Profiler;
		usleep(250000);
		$tmp = array_fill(0, 10000, 'testing is cool');
		$profiler->poll();
		$snapshots = $profiler->getSnapshots();
		$first = reset($snapshots);
		$last = end($snapshots);

		$time = $last['execution_time'] - $first['execution_time'];

		// usleep sometimes isnt very accurate so we use a fair window
		$this->assertGreaterThanOrEqual($first['execution_time'], $last['execution_time']);
		$this->assertGreaterThanOrEqual($first['memory_usage_peak'], $last['memory_usage_peak']);
	}

	public function testAbsoluteSnapshots()
	{
		// sleep before we create the profiler
		usleep(250000);
		$profiler = new Profiler(null, null, false);
		$profiler->poll();
		$snapshots = $profiler->getSnapshots();
		$first = reset($snapshots);
		$last = end($snapshots);

		$time = $last['execution_time'] - $first['execution_time'];

		// usleep sometimes isnt very accurate so we use a fair window
		$this->assertEquals(0, $time, null, 0.25);
	}

	public function testBasicHtmlOutput()
	{
		// sleep before we create the profiler
		$profiler = new Profiler;
		usleep(250000);

		// usleep sometimes isnt very accurate so we use a fair window
		$this->assertContains('profiler-', $profiler->renderHtml());
	}

	public function testExtremeNumbersInBasicHtmlOutput()
	{
		// sleep before we create the profiler
		$profiler = new Profiler(null, function() { return 1000000; }, false);
		$profiler->poll();

		// usleep sometimes isnt very accurate so we use a fair window
		$this->assertContains('9F0251', $profiler->renderHtml());
	}

	public function testQueryCountCallback()
	{
		$q = 0;
		$profiler = new Profiler(null, function() use(&$q) {
			return $q;
		});

		$this->assertEquals(0, $profiler->getNumberOfQueries());

		$q = 123;
		$this->assertEquals(123, $profiler->getNumberOfQueries());
	}
}
