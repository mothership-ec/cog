<?php

namespace Message\Cog\Test\AssetManagement;

use Message\Cog\AssetManagement\CssCoguleRewriteFilter as Filter;
use Mockery as m;

class CssCoguleRewriteFilter extends \PHPUnit_Framework_TestCase
{
	
	public function testRelativePath() {
		$asset = m::mock('\\Assetic\\Asset\\AssetInterface')
			->shouldReceive('getSourceRoot')
			->zeroOrMoreTimes()
			->andReturn('/test')

			->shouldReceive('getSourcePath')
			->zeroOrMoreTimes()
			->andReturn('test')

			->shouldReceive('getTargetPath')
			->zeroOrMoreTimes()
			->andReturn('/test/target')

			->shouldReceive('getContent')
			->once()
			->andReturn("url('../image.jpg')")

			->shouldReceive('setContent')
			->with('url(\'../../cogules/Test:Module/image.jpg\')')
			->once()

			->getMock()
		;

		$asset->cogNamespace = 'Test:Module';

		$filter = new Filter;

		$filter->filterDump($asset);
	}

	public function testAbsolutePath() {
		$asset = m::mock('\\Assetic\\Asset\\AssetInterface')
			->shouldReceive('getSourceRoot')
			->zeroOrMoreTimes()
			->andReturn('/test')

			->shouldReceive('getSourcePath')
			->zeroOrMoreTimes()
			->andReturn('test')

			->shouldReceive('getTargetPath')
			->zeroOrMoreTimes()
			->andReturn('/test/target')

			->shouldReceive('getContent')
			->once()
			->andReturn("url('/image.jpg')")

			->shouldReceive('setContent')
			->with('url(\'/image.jpg\')')
			->once()

			->getMock()
		;

		$asset->cogNamespace = 'Test:Module';

		$filter = new Filter;

		$filter->filterDump($asset);
	}

	public function testAbsolutePathWithProtocol() {
		$asset = m::mock('\\Assetic\\Asset\\AssetInterface')
			->shouldReceive('getSourceRoot')
			->zeroOrMoreTimes()
			->andReturn('/test')

			->shouldReceive('getSourcePath')
			->zeroOrMoreTimes()
			->andReturn('test')

			->shouldReceive('getTargetPath')
			->zeroOrMoreTimes()
			->andReturn('/test/target')

			->shouldReceive('getContent')
			->once()
			->andReturn("url('http://image.jpg')")

			->shouldReceive('setContent')
			->with('url(\'http://image.jpg\')')
			->once()

			->getMock()
		;

		$asset->cogNamespace = 'Test:Module';

		$filter = new Filter;

		$filter->filterDump($asset);
	}
}