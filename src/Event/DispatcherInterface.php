<?php

namespace Message\Cog\Event;

/**
 * Empty wrapper around Symfony's `EventDispatcherInterface` interface so we're
 * not exposing any Symfony code to the rest of Cog.
 */
interface DispatcherInterface extends \Symfony\Component\EventDispatcher\EventDispatcherInterface
{

}