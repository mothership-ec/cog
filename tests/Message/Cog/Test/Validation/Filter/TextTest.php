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
		$this->assertSame('NELSON', $this->_filter->uppercase('nelson'));
	}

	public function testUppercaseFromUppercase()
	{
		$this->assertSame('NELSON', $this->_filter->uppercase('NELSON'));
	}

	public function testUppercaseFromMixed()
	{
		$this->assertSame('NELSON', $this->_filter->uppercase('NeLsOn'));
	}

	public function testUppercaseFromInt()
	{
		$this->assertSame('1', $this->_filter->uppercase(1));
		$this->assertSame('1996', $this->_filter->uppercase(1996));
	}

	public function testUppercaseFromFloat()
	{
		$this->assertSame('1.4', $this->_filter->uppercase(1.4));
		$this->assertSame('0', $this->_filter->uppercase(0.0));
	}

	public function testLowercaseFromUppercase()
	{
		$this->assertSame('mandela', $this->_filter->lowercase('MANDELA'));
	}

	public function testLowercaseFromLowercase()
	{
		$this->assertSame('mandela', $this->_filter->lowercase('mandela'));
	}

	public function testLowercaseFromMixed()
	{
		$this->assertSame('mandela', $this->_filter->lowercase('MaNdElA'));
	}

	public function testLowercaseFromInt()
	{
		$this->assertSame('1', $this->_filter->lowercase(1));
		$this->assertSame('1986', $this->_filter->lowercase(1986));
	}

	public function testLowercaseFromFloat()
	{
		$this->assertSame('1.4', $this->_filter->lowercase(1.4));
		$this->assertSame('0', $this->_filter->lowercase(0.0));
	}

	public function testTitlecaseFromLower()
	{
		$this->assertSame('Dr. Dre', $this->_filter->titlecase('dr. dre'));
	}

	public function testTitlecaseFromUpper()
	{
		$this->assertSame('Dr. Dre', $this->_filter->titlecase('DR. DRE'));
	}

	public function testTitlecaseFromMixed()
	{
		$this->assertSame('Dr. Dre', $this->_filter->titlecase('DR. drE'));
	}

	public function testTitlecaseMaintainCase()
	{
		$this->assertSame('Dr. DrE', $this->_filter->titlecase('dr. drE', true));
	}

	public function testTitlecaseScottishName()
	{
		$this->assertSame('Mr. MacGuffin', $this->_filter->titlecase('mr. macguffin'));
		$this->assertSame('Mr. McGuffin', $this->_filter->titlecase('mr. mcguffin'));
	}

	public function testTitlecaseWithIgnores()
	{
		$this->assertSame('Strangers on a Train', $this->_filter->titlecase('strangers on a train'));
		$this->assertSame('The Curious Incident of the Dog in the Night', $this->_filter->titlecase('the curious incident of the dog in the night'));

	}
	
	public function testTitlecaseWithInt()
	{
		$this->assertSame('1', $this->_filter->titlecase(1));
		$this->assertSame('1986', $this->_filter->titlecase(1986));
	}

	public function testPrefixNoDelim()
	{
		$this->assertSame('hillside', $this->_filter->prefix('side', 'hill'));
	}

	public function testPrefixDelim()
	{
		$this->assertSame('very angry', $this->_filter->prefix('angry', 'very', ' '));
	}

	public function testPrefixWithInt()
	{
		$this->assertSame('12', $this->_filter->prefix(2, 1));
	}

	public function testPrefixWithFloat()
	{
		$this->assertSame('1.22.1', $this->_filter->prefix(2.1, 1.2));
	}

	public function testSuffixNoDelim()
	{
		$this->assertSame('hillside', $this->_filter->suffix('hill', 'side'));
	}

	public function testSuffixDelim()
	{
		$this->assertSame('very angry', $this->_filter->suffix('very', 'angry', ' '));
	}

	public function testSuffixWithInt()
	{
		$this->assertSame('12', $this->_filter->suffix(1, 2));
	}

	public function testSuffixWithFloat()
	{
		$this->assertSame('1.22.1', $this->_filter->suffix(1.2, 2.1));
	}

	public function testTrim()
	{
		$this->assertSame('hello', $this->_filter->trim(' hello '));
	}

	public function testTrimWithChars()
	{
		$this->assertSame('hello', $this->_filter->trim('/hello/', '/'));
	}

	public function testTrimWithInt()
	{
		$this->assertSame('1', $this->_filter->trim(1));
	}

	public function testTrimWithFloat()
	{
		$this->assertSame('1.2', $this->_filter->trim(1.2));
	}

	public function testRtrim()
	{
		$this->assertSame(' hello', $this->_filter->rtrim(' hello '));
	}

	public function testRtrimWithChars()
	{
		$this->assertSame('/hello', $this->_filter->rtrim('/hello/', '/'));
	}

	public function testRtrimWithInt()
	{
		$this->assertSame('1', $this->_filter->rtrim(1));
	}

	public function testRtrimWithFloat()
	{
		$this->assertSame('1.2', $this->_filter->rtrim(1.2));
	}

	public function testLtrim()
	{
		$this->assertSame('hello ', $this->_filter->ltrim(' hello '));
	}

	public function testLtrimWithChars()
	{
		$this->assertSame('hello/', $this->_filter->ltrim('/hello/', '/'));
	}

	public function testLtrimWithInt()
	{
		$this->assertSame('1', $this->_filter->ltrim(1));
	}

	public function testLtrimWithFloat()
	{
		$this->assertSame('1.2', $this->_filter->ltrim(1.2));
	}

	public function testCapitalizeFromLower()
	{
		$this->assertSame('Dr. Dre', $this->_filter->capitalize('dr. dre'));
	}

	public function testCapitalizeFromUpper()
	{
		$this->assertSame('Dr. Dre', $this->_filter->capitalize('DR. DRE'));
	}

	public function testCapitalizeFromMixed()
	{
		$this->assertSame('Dr. Dre', $this->_filter->capitalize('DR. dre'));
	}

	public function testCapitalizeMaintainCase()
	{
		$this->assertSame('Dr. DrE', $this->_filter->capitalize('dr. drE', true));
	}

	/**
	 * Test to ensure capitalize() behaves differently from titlecase()
	 */
	public function testCapitalizeTitlecaseIgnores()
	{
		$this->assertSame('The Dog In The Night', $this->_filter->capitalize('the dog in the night'));
	}

	public function testCapitalizeTitlecaseInt()
	{
		$this->assertSame('1', $this->_filter->titlecase(1));
	}

	public function testCapitalizeTitlecaseFloat()
	{
		$this->assertSame('1.2', $this->_filter->titlecase(1.2));
	}

	public function testReplace()
	{
		$this->assertSame('hell0 w0rld', $this->_filter->replace('hello world', 'o', '0'));
	}

	public function testReplaceWithInt()
	{
		$this->assertSame('hell0 w0rld', $this->_filter->replace('hello world', 'o', 0));
	}

	public function testReplaceWithFloat()
	{
		$this->assertSame('he1.1o world', $this->_filter->replace('hello world', 'll', 1.1));
	}

	public function testToUrlNoPrefix()
	{
		$this->assertSame('http://message.co.uk', $this->_filter->toUrl('message.co.uk'));
	}

	public function testToUrlExistingPrefix()
	{
		$this->assertSame('http://message.co.uk', $this->_filter->toUrl('http://message.co.uk'));
	}

	public function testToUrlDifferentPrefix()
	{
		$this->assertSame('https://message.co.uk', $this->_filter->toUrl('message.co.uk', 'https'));
	}

	public function testToUrlDifferentPrefixWithColonSlashes()
	{
		$this->assertSame('https://message.co.uk', $this->_filter->toUrl('message.co.uk', 'https://'));
	}

	public function testToUrlNoReplace()
	{
		$this->assertSame('https://message.co.uk', $this->_filter->toUrl('https://message.co.uk'));
	}

	public function testToUrlReplacePrefix()
	{
		$this->assertSame('https://message.co.uk', $this->_filter->toUrl('http://message.co.uk', 'https', true));
	}

	public function testToUrlWithInt()
	{
		$this->assertSame('http://1', $this->_filter->toUrl(1));
	}

	public function testToUrlWithFloat()
	{
		$this->assertSame('http://1.1', $this->_filter->toUrl(1.1));
	}

	public function testSlug()
	{
		$this->assertSame('kermit-the-frog', $this->_filter->slug('Kermit the Frog'));
	}

	public function testSlugWithSpecialChars()
	{
		$this->assertSame('s-l-u-g', $this->_filter->slug('S&%£l_u*g'));
	}

	public function testSlugAllSpecialChars()
	{
		$this->assertSame('n-a', $this->_filter->slug('*%&$&^^*%'));
	}

	public function testSlugWithInt()
	{
		$this->assertSame('1', $this->_filter->slug(1));
	}

	public function testSlugWithFloat()
	{
		$this->assertSame('1-1', $this->_filter->slug(1.1));
	}
}