<?php

namespace Message\Cog\Test\Filesystem;

use Message\Cog\Filesystem\StreamWrapperManager;
use Message\Cog\Filesystem\CogStreamWrapper;

use Message\Cog\ReferenceParser;
use Message\Cog\Test\Module;

use Message\Cog\Filesystem\Finder;


class StreamWrapperTest extends \PHPUnit_Framework_TestCase
{
	const DEFAULT_VENDOR = 'Message';
	const DEFAULT_MODULE = 'Cog';

	protected $_modulePaths = array(
		'Message\Cog'                   => '/path/to/installation/vendor/message/cog/src',
		'Message\CMS'                   => '/path/to/installation/vendor/message/cog-cms',
		'Commerce\Core'                 => '/path/to/installation/vendor/message/commerce',
		'Commerce\Epos'                 => '/path/to/installation/vendor/message/commerce',
		'UniformWares\CustomModuleName' => '/path/to/installation/app',
	);

	public function setUp()
	{

		$fnsUtility = $this->getMockBuilder('Message\\Cog\\Functions\\Utility')
			->disableOriginalConstructor()
			->getMock();

		// Set the default/traced vendor and module
		$fnsUtility
			->expects($this->any())
			->method('traceCallingModuleName')
			->will($this->returnValue(self::DEFAULT_VENDOR . '\\' . self::DEFAULT_MODULE));

		$this->parser = new ReferenceParser(
			new Module\FauxLocator($this->_modulePaths),
			$fnsUtility
		);

		$this->manager = new StreamWrapperManager();
	}

	public function tearDown()
	{
	//	$this->manager->clear();
	}

	public function testMapping()
	{
		# code...
	}
}