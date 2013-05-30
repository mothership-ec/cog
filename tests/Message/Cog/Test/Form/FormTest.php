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

	public function testCreateForm()
	{
		// Set up the CSRF provider
		$csrfProvider = new DefaultCsrfProvider('RANDOMNOISE');

		// Set up the Validator component
		$validator = Validation::createValidator();

		// Set up the Translation component
		/*
		$translator = new Translator('en');
		$translator->addLoader('xlf', new XliffFileLoader());
		$translator->addResource('xlf', __DIR__. '/../../../../src/Message/cog/Resources/translations/validators.en.xlf', 'en', 'validators');
		$translator->addResource('xlf', VENDOR_VALIDATOR_DIR . '/Resources/translations/validators.en.xlf', 'en', 'validators');
		*/

		// Set up Twig
		$twig = new Twig_Environment(new Twig_Loader_Filesystem(array(
		    VIEWS_DIR,
		    VENDOR_TWIG_BRIDGE_DIR . '/Resources/views/Form',
		)));
		$formEngine = new TwigRendererEngine(array(DEFAULT_FORM_THEME));
		$formEngine->setEnvironment($twig);
	//	$twig->addExtension(new TranslationExtension($translator));
		$twig->addExtension(new FormExtension(new TwigRenderer($formEngine, $csrfProvider)));

		// Set up the Form component
		$formFactory = Forms::createFormFactoryBuilder()
		    ->addExtension(new CsrfExtension($csrfProvider))
		    ->addExtension(new ValidatorExtension($validator))
		    ->getFormFactory();

	}

	public function testAdd()
	{
//		$this->_form->add('test');
	}
}