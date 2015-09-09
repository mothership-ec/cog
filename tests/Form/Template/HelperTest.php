<?php

namespace Message\Cog\Test\Form\Template;

use Message\Cog\Form\Template\Helper;

class HelperTest extends \PHPUnit_Framework_TestCase
{
	protected $_helper;

	protected $_renderer;

	protected $_formView;

	public function setUp()
	{
		$this->_renderer = $this->getMockBuilder('\\Symfony\\Bridge\\Twig\\Form\\TwigRenderer')
			->disableOriginalConstructor()
			->setMethods(array(
				'setTheme',
				'searchAndRenderBlock',
				'renderBlock',
				'renderCsrfToken',
				'humanize',
			))
			->getMock();

		$this->_helper = new Helper($this->_renderer);

		$this->_formView = $this->getMockBuilder('\\Symfony\\Component\\Form\\FormView')
			->disableOriginalConstructor()
			->getMock();
	}

	public function testGetName()
	{
		$this->assertSame('form', $this->_helper->getName());
	}

	public function testSetThemeWithArray()
	{
		$this->_renderer->expects($this->once())
			->method('setTheme');

		$this->_helper->setTheme($this->_formView, array('theme'));
	}

	public function testSetThemeWithString()
	{
		$this->_renderer->expects($this->once())
			->method('setTheme');

		$this->_helper->setTheme($this->_formView, 'theme');
	}

	public function testEnctype()
	{
		$this->_renderer->expects($this->once())
			->method('searchAndRenderBlock');

		$this->_helper->enctype($this->_formView);
	}

	public function testWidget()
	{
		$this->_renderer->expects($this->once())
			->method('searchAndRenderBlock');

		$this->_helper->widget($this->_formView);
	}

	public function testWidgetWithVars()
	{
		$this->_renderer->expects($this->once())
			->method('searchAndRenderBlock');

		$this->_helper->widget($this->_formView, array('bunch', 'of', 'vars'));
	}

	public function testRow()
	{
		$this->_renderer->expects($this->once())
			->method('searchAndRenderBlock');

		$this->_helper->row($this->_formView);
	}

	public function testRowWithVars()
	{
		$this->_renderer->expects($this->once())
			->method('searchAndRenderBlock');

		$this->_helper->row($this->_formView, array('bunch', 'of', 'vars'));
	}

	public function testLabel()
	{
		$this->_renderer->expects($this->once())
			->method('searchAndRenderBlock');

		$this->_helper->label($this->_formView);
	}

	public function testLabelWithLabel()
	{
		$this->_renderer->expects($this->once())
			->method('searchAndRenderBlock');

		$this->_helper->label($this->_formView, 'label');
	}

	public function testLabelWithVars()
	{
		$this->_renderer->expects($this->once())
			->method('searchAndRenderBlock');

		$this->_helper->label($this->_formView, null, array('bunch', 'of', 'vars'));
	}

	public function testLabelWithLabelAndVars()
	{
		$this->_renderer->expects($this->once())
			->method('searchAndRenderBlock');

		$this->_helper->label($this->_formView, 'label', array('bunch', 'of', 'vars'));
	}

	public function testErrors()
	{
		$this->_renderer->expects($this->once())
			->method('searchAndRenderBlock');

		$this->_helper->errors($this->_formView);
	}

	public function testRest()
	{
		$this->_renderer->expects($this->once())
			->method('searchAndRenderBlock');

		$this->_helper->rest($this->_formView);
	}

	public function testRestWithVars()
	{
		$this->_renderer->expects($this->once())
			->method('searchAndRenderBlock');

		$this->_helper->rest($this->_formView, array('bunch', 'of', 'vars'));
	}

	public function testBlock()
	{
		$this->_renderer->expects($this->once())
			->method('renderBlock');

		$this->_helper->block($this->_formView, 'name');
	}

	public function testBlockWithVars()
	{
		$this->_renderer->expects($this->once())
			->method('renderBlock');

		$this->_helper->block($this->_formView, 'name', array('bunch', 'of', 'vars'));
	}

	public function testCsrfToken()
	{
		$this->_renderer->expects($this->once())
			->method('renderCsrfToken');

		$this->_helper->csrfToken('intention');
	}

	public function testHumanize()
	{
		$this->_renderer->expects($this->once())
			->method('humanize');

		$this->_helper->humanize('text');
	}
}