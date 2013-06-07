<?php

namespace Message\Cog\Test\Form;

use Message\Cog\Form\EventListener;
use Message\Cog\Test\Service\FauxContainer;

class EventListenerTest extends \PHPUnit_Framework_TestCase
{

	public function testSubscribedEvents()
	{
		$subscriptions = EventListener::getSubscribedEvents();

		$this->assertArrayHasKey('modules.load.success', $subscriptions);
		$this->assertContains(array('setupFormHelper'), $subscriptions['modules.load.success']);
	}

	// @todo test fails!
	public function testSetupFormHelper()
	{
		$services   = new FauxContainer;
		$listener   = new EventListener;

		$phpTemplatingEngine = $this->getMockBuilder('\\\Message\\Cog\\Templating\\PhpEngine')
			->disableOriginalConstructor()
			->setMethods(array('addHelpers'))
			->getMock();

		$phpFormHelper = $this->getMockBuilder('\\Message\\Cog\\Form\\Template\\Helper')
			->disableOriginalConstructor()
			->getMock();

		$services['templating.engine.php'] = $phpTemplatingEngine;
		$services['form.helper.php'] = $phpFormHelper;

		$listener->setContainer($services);

		$phpFormHelper->expects($this->once())
			->method('addHelpers');

		$listener->setupFormHelper();
	}

}