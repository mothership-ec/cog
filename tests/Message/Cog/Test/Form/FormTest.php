<?php

namespace Message\Cog\Test\Form;

use Message\Cog\Test\Event\FauxDispatcher;
//use Message\Cog\Form\Form;
use Symfony\Component\Form\Form;
use Message\Cog\Form\Data;
use Message\Cog\Service\Container;

use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Extension\Templating\TemplatingExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateReference;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\Loader\FilesystemLoader;

class FormTest extends \PHPUnit_Framework_TestCase
{
	protected $_configBuilder;
	protected $_form;

	public function setUp()
	{
//		$this->_eventDispatcher = new FauxDispatcher;
//
//		$this->_configBuilder = $this->getMock(
//			'\\Message\\Cog\\Form\\ConfigBuilder',
//			array('getCompound'),
//			array('test', '\\Message\\Cog\\Form\\Data', $this->_eventDispatcher));
//
//		$this->_form = new Form($this->_configBuilder);
	}

	public function testCreateForm()
	{
		$config = new \Message\Cog\Form\ConfigBuilder(
			'test',
			null,
			new FauxDispatcher()
		);

		$csrfSecret = 'bob';

		$engine = new PhpEngine(new SimpleTemplateNameParser(realpath(__DIR__ . '/../views')), new FilesystemLoader(array()));

		$formFactory = Forms::createFormFactoryBuilder()
			->addExtension(new CsrfExtension(new DefaultCsrfProvider($csrfSecret)))
			->addExtension(new TemplatingExtension($engine, null, array(
// Will hopefully not be necessary anymore in 2.2
				realpath(__DIR__ . '/../vendor/symfony/framework-bundle/Symfony/Bundle/FrameworkBundle/Resources/views/Form'),
			)))
			->getFormFactory();

		$config->setCompound(true);

		$form = new Form($config);

		$form->add('email', 'email')
			->add('siteUrl', 'url');

		var_dump($form->getForm());
	}

	public function testAdd()
	{
//		$this->_form->add('test');
	}
}