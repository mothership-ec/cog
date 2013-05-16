<?php

namespace Message\Cog\Test;

use Message\Cog\ReferenceParser;

class ReferenceParserTest extends \PHPUnit_Framework_TestCase
{
	const DEFAULT_VENDOR = 'Message';
	const DEFAULT_MODULE = 'Cog';

	protected $_parser;
	protected $_modulePaths = array(
		'Message\Cog'                   => '/path/to/installation/vendor/message/cog/src',
		'Message\Mothership\CMS'        => '/path/to/installation/vendor/message/cog-cms',
		'Commerce\Core'                 => '/path/to/installation/vendor/message/commerce',
		'Commerce\Epos'                 => '/path/to/installation/vendor/message/commerce',
		'UniformWares\CustomModuleName' => '/path/to/installation/app',
	);

	public function setUp()
	{
		$fnsUtility = $this->getMockBuilder('Message\Cog\Functions\Utility')
			->disableOriginalConstructor()
			->getMock();

		// Set the default/traced vendor and module
		$fnsUtility
			->expects($this->any())
			->method('traceCallingModuleName')
			->will($this->returnValue(self::DEFAULT_VENDOR . '\\' . self::DEFAULT_MODULE));

		$this->_parser = new ReferenceParser(
			new Module\FauxLocator($this->_modulePaths),
			$fnsUtility
		);
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage No reference has been parsed yet
	 */
	public function testGetSymfonyLogicalControllerNameThrowsExceptionWhenNoReferenceSet()
	{
		$this->_parser->getSymfonyLogicalControllerName();
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage No reference has been parsed yet
	 */
	public function testGetFullPathThrowsExceptionWhenNoReferenceSet()
	{
		$this->_parser->getFullPath();
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage No reference has been parsed yet
	 */
	public function testGetClassNameThrowsExceptionWhenNoReferenceSet()
	{
		$this->_parser->getClassName();
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage No reference has been parsed yet
	 */
	public function testGetAllPartsThrowsExceptionWhenNoReferenceSet()
	{
		$this->_parser->getAllParts();
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage No reference has been parsed yet
	 */
	public function testIsRelativeThrowsExceptionWhenNoReferenceSet()
	{
		$this->_parser->isRelative();
	}

	/**
	 * @dataProvider getValidReferences
	 */
	public function testGetSymfonyLogicalControllerName($reference, $allParts)
	{
		$parsed   = $this->_parser->parse($reference);
		$expected = $allParts['vendor'] . '\\' .
					$allParts['module'] . '\\' .
					implode('\\', $allParts['path']);

		if (!is_null($allParts)) {
			$expected .= '::' . $allParts['method'];
		}

		$this->assertEquals($expected, $parsed->getSymfonyLogicalControllerName());
	}

	/**
	 * @dataProvider getValidReferences
	 */
	public function testGetPathAndFullPath($reference, $allParts)
	{
		$parsed     = $this->_parser->parse($reference);
		$relative   = implode(DIRECTORY_SEPARATOR, $allParts['path']);
		$modulePath = $this->_modulePaths[$allParts['vendor'] . '\\' . $allParts['module']];

		$this->assertEquals(
			implode(DIRECTORY_SEPARATOR, $allParts['path']),
			$parsed->getPath(null, DIRECTORY_SEPARATOR)
		);

		$this->assertEquals(
			implode(DIRECTORY_SEPARATOR, $allParts['path']),
			$parsed->getPath()
		);

		$this->assertEquals(
			'SomeFolder-' . implode('-', $allParts['path']),
			$parsed->getPath('SomeFolder', '-')
		);

		$this->assertEquals(
			'SomeFolder/SomeOtherFolder/' . implode('/', $allParts['path']),
			$parsed->getPath(array('SomeFolder', 'SomeOtherFolder'), '/')
		);

		$this->assertEquals(
			implode(DIRECTORY_SEPARATOR, array(
				$modulePath,
				implode(DIRECTORY_SEPARATOR, $allParts['path'])
			)),
			$parsed->getFullPath()
		);

		$this->assertEquals(
			implode(DIRECTORY_SEPARATOR, array(
				$modulePath,
				'Controller',
				implode(DIRECTORY_SEPARATOR, $allParts['path'])
			)),
			$parsed->getFullPath('Controller')
		);

		$this->assertEquals(
			implode(DIRECTORY_SEPARATOR, array(
				$modulePath,
				'Controller',
				'MyFolder',
				implode(DIRECTORY_SEPARATOR, $allParts['path'])
			)),
			$parsed->getFullPath(array('Controller', 'MyFolder'))
		);
	}

	/**
	 * @dataProvider getValidReferences
	 */
	public function testGetClassName($reference, $allParts)
	{
		$parsed   = $this->_parser->parse($reference);
		$expected = $allParts['vendor'] . '\\' .
					$allParts['module'] . '\\' .
					implode('\\', $allParts['path']);

		$this->assertEquals($expected, $parsed->getClassName());

		$expected = $allParts['vendor'] . '\\' .
					$allParts['module'] . '\\' .
					'Controller\\TestNamespace\\' .
					implode('\\', $allParts['path']);

		$this->assertEquals($expected, $parsed->getClassName(array('Controller', 'TestNamespace')));

		$expected = $allParts['vendor'] . '\\' .
					$allParts['module'] . '\\' .
					'Namespace\\' .
					implode('\\', $allParts['path']);

		$this->assertEquals($expected, $parsed->getClassName('Namespace'));
	}

	/**
	 * @dataProvider getValidReferences
	 */
	public function testAllParts($reference, $allParts)
	{
		$parsed = $this->_parser->parse($reference);

		$this->assertEquals($parsed->getAllParts(), $allParts);
	}

	/**
	 * @dataProvider getValidReferences
	 */
	public function testGetModuleName($reference, $allParts)
	{
		$parsed = $this->_parser->parse($reference);

		$this->assertEquals($allParts['vendor'] . '\\' . $allParts['module'], $parsed->getModuleName());
	}

	/**
	 * @dataProvider getRelativeReferences
	 */
	public function testIsRelativePositive($reference, $allParts)
	{
		$parsed = $this->_parser->parse($reference);

		$this->assertTrue($parsed->isRelative());
	}

	/**
	 * @dataProvider getAbsoluteReferences
	 */
	public function testIsRelativeNegative($reference, $allParts)
	{
		$parsed = $this->_parser->parse($reference);

		$this->assertFalse($parsed->isRelative());
	}

	public function testParseReturnsSelf()
	{
		$return = $this->_parser->parse('::ClassFolder:ClassName#methodName');

		$this->assertEquals($this->_parser, $return);
	}

	/**
	 * @dataProvider      getInvalidReferences
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidReferenceThrowsException($reference)
	{
		$this->_parser->parse($reference);
	}

	/**
	 * @dataProvider getRelativeReferences
	 */
	public function testRelativeReferenceWorks($reference, $allParts)
	{
		$parsed = $this->_parser->parse($reference);

		$this->assertEquals(self::DEFAULT_VENDOR, $allParts['vendor']);
		$this->assertEquals(self::DEFAULT_MODULE, $allParts['module']);
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage No reference has been parsed yet
	 */
	public function testClear()
	{
		$parsed = $this->_parser->parse('Vendor:Module::Path:To:Something');

		$parsed->clear();

		$parsed->getModuleName();
	}


	static public function getRelativeReferences()
	{
		return array(
			array(
				'::ClassFolder:ClassName#methodName',
				array(
					'vendor' => 'Message',
					'module' => 'Cog',
					'path'   => array(
						'ClassFolder',
						'ClassName',
					),
					'method' => 'methodName',
				)
			),
			array(
				'::View',
				array(
					'vendor' => 'Message',
					'module' => 'Cog',
					'path'   => array(
						'View',
					),
					'method' => null,
				)
			),
			array(
				'::Controllers:View',
				array(
					'vendor' => 'Message',
					'module' => 'Cog',
					'path'   => array(
						'Controllers',
						'View',
					),
					'method' => null,
				)
			),
			array(
				'::FileName#index',
				array(
					'vendor' => 'Message',
					'module' => 'Cog',
					'path'   => array(
						'FileName',
					),
					'method' => 'index',
				)
			),
		);
	}

	static public function getAbsoluteReferences()
	{
		return array(
			array(
				'UniformWares:CustomModuleName::ClassFile',
				array(
					'vendor' => 'UniformWares',
					'module' => 'CustomModuleName',
					'path'   => array(
						'ClassFile',
					),
					'method' => null,
				)
			),
			array(
				'Message:Mothership:CMS::Controller:Private#view',
				array(
					'vendor' => 'Message',
					'module' => 'Mothership\CMS',
					'path'   => array(
						'Controller',
						'Private',
					),
					'method' => 'view',
				)
			),
			array(
				'Commerce:Epos::Till:Return:View',
				array(
					'vendor' => 'Commerce',
					'module' => 'Epos',
					'path'   => array(
						'Till',
						'Return',
						'View',
					),
					'method' => null,
				)
			),
			array(
				'Commerce:Core::Admin#List',
				array(
					'vendor' => 'Commerce',
					'module' => 'Core',
					'path'   => array(
						'Admin',
					),
					'method' => 'List',
				)
			),
		);
	}

	static public function getValidReferences()
	{
		return array_merge(
			self::getRelativeReferences(),
			self::getAbsoluteReferences()
		);
	}

	static public function getInvalidReferences()
	{
		return array(
			array(
				'not-really-a REFERENCE'
			),
		);
	}
}