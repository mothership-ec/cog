<?php

namespace Message\Cog\Event;

/*
 * Empty wrapper around Symfony's `EventSubscriberInterface` interface so we're
 * not exposing any Symfony code to the rest of Cog.
 */
interface SubscriberInterface extends \Symfony\Component\EventDispatcher\EventSubscriberInterface
{

}