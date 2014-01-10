<?php

namespace Message\Cog\Event;

/*
 * Empty wrapper around Symfony's `Event` class so we're not exposing any
 * Symfony code to the rest of Cog.
 */
class Event extends \Symfony\Component\EventDispatcher\Event
{

}