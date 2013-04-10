<?php

namespace Message\Cog\Test\Validation\Filter;

use Message\Cog\Validation\Filter\Text;

class TextTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_filter = new Text;
	}

	public function testUppercaseFromLowercase()
	{
		$this->assertEquals('NELSON', $this->_filter->uppercase('nelson'));
	}

	public function testUppercaseFromUppercase()
	{
		$this->assertEquals('NELSON', $this->_filter->uppercase('NELSON'));
	}

	public function testUppercaseFromMixed()
	{
		$this->assertEquals('NELSON', $this->_filter->uppercase('NeLsOn'));
	}

	/**
	 * Test to ensure exception is thrown
	 */
	public function testUppercaseFromNonString()
	{
		try {
			$this->_filter->uppercase(true);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testLowercaseFromUppercase()
	{
		$this->assertEquals('mandela', $this->_filter->lowercase('MANDELA'));
	}

	public function testLowercaseFromLowercase()
	{
		$this->assertEquals('mandela', $this->_filter->lowercase('mandela'));
	}

	public function testLowercaseFromMixed()
	{
		$this->assertEquals('mandela', $this->_filter->lowercase('MaNdElA'));
	}

	/**
	 * Test to ensure exception is thrown
	 */
	public function testLowercaseFromNonString()
	{
		try {
			$this->_filter->lowercase(true);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testTitlecaseFromLower()
	{
		$this->assertEquals('Dr. Dre', $this->_filter->titlecase('dr. dre'));
	}

	public function testTitlecaseFromUpper()
	{
		$this->assertEquals('Dr. Dre', $this->_filter->titlecase('DR. DRE'));
	}

	public function testTitlecaseFromMixed()
	{
		$this->assertEquals('Dr. Dre', $this->_filter->titlecase('DR. dre'));
	}

	public function testTitlecaseMaintainCase()
	{
		$this->assertEquals('Dr. DrE', $this->_filter->titlecase('dr. drE', true));
	}

	/**
	 * Test to ensure exception is thrown
	 */
	public function testTitlecaseFromNonString()
	{
		try {
			$this->_filter->titlecase(true);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testPrefixNoDelim()
	{
		$this->assertEquals('hillside', $this->_filter->prefix('side', 'hill'));
	}

	public function testPrefixDelim()
	{
		$this->assertEquals('very angry', $this->_filter->prefix('angry', 'very', ' '));
	}

	public function testPrefixNonString()
	{
		try {
			$this->_filter->prefix('hello', true);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testSuffixNoDelim()
	{
		$this->assertEquals('hillside', $this->_filter->suffix('hill', 'side'));
	}

	public function testSuffixDelim()
	{
		$this->assertEquals('very angry', $this->_filter->suffix('very', 'angry', ' '));
	}

	/**
	 * Test to ensure exception is thrown
	 */
	public function testSuffixNonString()
	{
		try {
			$this->_filter->suffix('hello', true);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testTrim()
	{
		$this->assertEquals('hello', $this->_filter->trim(' hello '));
	}

	public function testTrimWithChars()
	{
		$this->assertEquals('hello', $this->_filter->trim('/hello/', '/'));
	}

	/**
	 * Test to ensure exception is thrown
	 */
	public function testTrimNonString()
	{
		try {
			$this->_filter->trim(true);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testRtrim()
	{
		$this->assertEquals(' hello', $this->_filter->rtrim(' hello '));
	}

	public function testRtrimWithChars()
	{
		$this->assertEquals('/hello', $this->_filter->rtrim('/hello/', '/'));
	}

	/**
	 * Test to ensure exception is thrown
	 */
	public function testRtrimNonString()
	{
		try {
			$this->_filter->rtrim(true);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testLtrim()
	{
		$this->assertEquals('hello ', $this->_filter->ltrim(' hello '));
	}

	public function testLtrimWithChars()
	{
		$this->assertEquals('hello/', $this->_filter->ltrim('/hello/', '/'));
	}

	/**
	 * Test to ensure exception is thrown
	 */
	public function testLtrimNonString()
	{
		try {
			$this->_filter->ltrim(true);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	/**
	 * @todo add capitalize tests once the method is made different from titlecase()
	 */

	public function testReplace()
	{
		$this->assertEquals('hell0 w0rld', $this->_filter->replace('hello world', 'o', '0'));
	}

	public function testReplaceWithInt()
	{
		$this->assertEquals('hell0 w0rld', $this->_filter->replace('hello world', 'o', 0));

	}

	/**
	 * Test to ensure exception is thrown
	 */
	public function testReplaceWithNonString()
	{
		try {
			$this->_filter->replace('hello', 'o', false);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}
}