<?php

namespace Message\Cog\Test\Config;

use Message\Cog\Config\Compiler;
use Message\Cog\Config\Group;

class CompilerTest extends \PHPUnit_Framework_TestCase
{
	public function testAdding()
	{
		$compiler = new Compiler;
		$this->assertEquals($compiler, $compiler->add('test: yes'));

		// NOTE: an exception would get thrown here if `add()` didn't work
		$compiler->compile();
	}

	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage nothing to compile
	 */
	public function testClearing()
	{
		$compiler = new Compiler;

		$compiler->add('test: yes');
		$compiler->add('test: no');
		$compiler->add('property: value');

		$compiler->clear();

		$compiler->compile();
	}

	/**
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage nothing to compile
	 */
	public function testCompilingOnEmptyContainer()
	{
		$compiler = new Compiler;
		$compiler->compile();
	}

	public function testCompilation()
	{
		$compiler = new Compiler;

		$compiler->add(file_get_contents(__DIR__ . '/fixtures/example.yml'));
		$compiler->add(file_get_contents(__DIR__ . '/fixtures/live/example.yml'));

		$compiled = $compiler->compile();

		$expected = include 'expected_groups.php';

		$this->assertInstanceOf('Message\Cog\Config\Group', $compiled);
		$this->assertEquals($expected['example'], $compiled);
	}

	/**
	 * @dataProvider getValidYAMLButNotArray
	 *
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage parsed result was not an array for YAML
	 */
	public function testCompilingValidYAMLButNotArray($data)
	{
		$compiler = new Compiler;

		$compiler->add($data);

		$compiler->compile();
	}

	/**
	 * @dataProvider getInvalidYAML
	 *
	 * @expectedException        Message\Cog\Config\Exception
	 * @expectedExceptionMessage YAML parsing failed
	 */
	public function testCompilingInvalidYAML($data)
	{
		$compiler = new Compiler;

		$compiler->add($data);

		$compiler->compile();
	}

	static public function getValidYAMLButNotArray()
	{
		return array(
			array('this 	S IS N*%^$^%£@$&%^*$£OT YAML'),
			array(false),
			array(null),
			array('iamyaml'),
		);
	}

	static public function getInvalidYAML()
	{
		return array(
			array('Bad escapes:
  "\c
  \xq-"'),
			array('- Invalid use of BOM
⇔
- Inside a document.'),
			array('		I like to use tabs
						- to indent my YAML'),
		);
	}
}