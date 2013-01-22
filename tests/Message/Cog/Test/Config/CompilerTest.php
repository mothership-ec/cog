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
		$expected = new Group;

		$expected->name                  = 'Message';
		$expected->shortName             = 'message';
		$expected->domain                = 'message.co.uk';
		$expected->url                   = 'live.message.co.uk';
		$expected->email                 = 'debug@message.co.uk';
		$expected->telephone             = '+44 12345 6789';
		$expected->fax                   = null;
		$expected->vatRegistrationNumber = '0123456789';
		$expected->companyNumber         = '1234567';
		$expected->facebook              = 'message';
		$expected->twitter               = 'messagedigital';
		$expected->gaCode                = 'MESSAGE_123';

		$expected->address = (object) array(
			'line1'       => 'Atlas Chambers',
			'line2'       => '33 West Street',
			'town'        => 'Hove',
			'postcode'    => 'BN1 2RE',
			'countryCode' => 'GB',
			'country'     => 'United Kingdom',
		);

		$expected->gateway = (object) array(
			'useLocalPayment' => true,
			'sagepay'         => (object) array(
				'vendor'          => 'message_live',
				'vpsProtocol'     => 1.445,
				'encryptionKey'   => 1234567,
				'paymentUrl'      => 'https://test.sagepay.com/gateway/service/vspserver-register.vsp',
				'refundUrl'       => 'https://test.sagepay.com/gateway/service/refund.vsp',
				'redirectUrlBase' => 'http://beta.message.co.uk',
			),
		);

		$expected->updates = (object) array(
			'email'  => true,
			'postal' => false,
		);

		$expected->admins = array(
			'Mark Bobkins',
			'Bob Smith',
		);

		$this->assertInstanceOf('Message\Cog\Config\Group', $compiled);
		$this->assertEquals($expected, $compiled);
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