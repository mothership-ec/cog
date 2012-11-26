<?php

namespace Message\Cog\Event;

/**
 * Empty wrapper around Symfony's `EventDispatcher` class so we're not exposing
 * any Symfony code to the rest of Cog.
 *
 * This extends our own `DispatcherInterface` which can be used for type
 * hinting.
 */
class Dispatcher extends \Symfony\Component\EventDispatcher\EventDispatcher implements DispatcherInterface
{

}