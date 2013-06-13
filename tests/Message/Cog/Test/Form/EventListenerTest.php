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

	public function testSetupFormHelper()
	{
		$services   = new FauxContainer;
		$listener   = new EventListener;

		$this->assertInstanceOf('\\Message\\Cog\\Event\\SubscriberInterface', $listener);
		$this->assertInstanceOf('\\Message\\Cog\\Event\\EventListener', $listener);

		$phpTemplatingEngine = $this->getMockBuilder('\\Message\\Cog\\Templating\\PhpEngine')
			->disableOriginalConstructor()
			->setMethods(array('addHelpers'))
			->getMock();

		$phpFormHelper = $this->getMockBuilder('\\Message\\Cog\\Form\\Template\\Helper')
			->disableOriginalConstructor()
			->getMock();

		$services['templating.php.engine'] = $phpTemplatingEngine;
		$services['form.helper.php'] = $phpFormHelper;

		$listener->setContainer($services);

		$phpTemplatingEngine->expects($this->once())
			->method('addHelpers');

		$listener->setupFormHelper();
	}

}