<?php

namespace Message\Cog\Test\Validation\Filter;

use Message\Cog\Validation\Filter\Text;

class TextTest extends \PHPUnit_Framework_TestCase
{
	protected $_filter;

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
		$this->assertEquals('Dr. Dre', $this->_filter->titlecase('DR. drE'));
	}

	public function testTitlecaseMaintainCase()
	{
		$this->assertEquals('Dr. DrE', $this->_filter->titlecase('dr. drE', true));
	}

	public function testTitlecaseScottishName()
	{
		$this->assertEquals('Mr. MacGuffin', $this->_filter->titlecase('mr. macguffin'));
	}

	public function testTitlecaseWithIgnores()
	{
		$this->assertEquals('Strangers on a Train', $this->_filter->titlecase('strangers on a train'));
		$this->assertEquals('The Curious Incident of the Dog in the Night', $this->_filter->titlecase('the curious incident of the dog in the night'));

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

	public function testCapitalizeFromLower()
	{
		$this->assertEquals('Dr. Dre', $this->_filter->capitalize('dr. dre'));
	}

	public function testCapitalizeFromUpper()
	{
		$this->assertEquals('Dr. Dre', $this->_filter->capitalize('DR. DRE'));
	}

	public function testCapitalizeFromMixed()
	{
		$this->assertEquals('Dr. Dre', $this->_filter->capitalize('DR. dre'));
	}

	public function testCapitalizeMaintainCase()
	{
		$this->assertEquals('Dr. DrE', $this->_filter->capitalize('dr. drE', true));
	}

	/**
	 * Test to ensure capitalize() behaves differently from titlecase()
	 */
	public function testCapitalizeTitlecaseIgnores()
	{
		$this->assertEquals('The Dog In The Night', $this->_filter->capitalize('the dog in the night'));
	}

	/**
	 * Test to ensure exception is thrown
	 */
	public function testCapitalizeNonString()
	{
		try {
			$this->_filter->capitalize(true);
		}
		catch (\Exception $e) {
			return;
		}

		$this->fail('Exception not thrown');
	}

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

	public function testUrlNoPrefix()
	{
		$this->assertEquals('http://message.co.uk', $this->_filter->url('message.co.uk'));
	}

	public function testUrlExistingPrefix()
	{
		$this->assertEquals('http://message.co.uk', $this->_filter->url('http://message.co.uk'));
	}

	public function testUrlDifferentPrefix()
	{
		$this->assertEquals('https://message.co.uk', $this->_filter->url('message.co.uk', 'https'));
	}

	public function testUrlDifferentPrefixWithColonSlashes()
	{
		$this->assertEquals('https://message.co.uk', $this->_filter->url('message.co.uk', 'https://'));
	}

	public function testUrlNoReplace()
	{
		$this->assertEquals('https://message.co.uk', $this->_filter->url('https://message.co.uk'));
	}

	public function testUrlReplacePrefix()
	{
		$this->assertEquals('https://message.co.uk', $this->_filter->url('http://message.co.uk', 'https', true));
	}

	/**
	 * Test to ensure exception is thrown
	 */
	public function testUrlWithNonString()
	{
		try {
			$this->_filter->url(null);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}

	public function testSlug()
	{
		$this->assertEquals('kermit-the-frog', $this->_filter->slug('Kermit the Frog'));
	}

	public function testSlugWithSpecialChars()
	{
		$this->assertEquals('s-l-u-g', $this->_filter->slug('S&%£l_u*g'));
	}

	public function testSlugAllSpecialChars()
	{
		$this->assertEquals('n-a', $this->_filter->slug('*%&$&^^*%'));
	}

	/**
	 * Test to ensure exception is thrown
	 */
	public function testSlugNonString()
	{
		try {
			$this->_filter->slug(true);
		}
		catch (\Exception $e) {
			return;
		}
		$this->fail('Exception not thrown');
	}
}