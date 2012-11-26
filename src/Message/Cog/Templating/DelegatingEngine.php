<?php

namespace Message\Cog\Templating;

/**
 * Empty wrapper around Symfony's `DelegatingEngine` class so we're not exposing
 * any Symfony code to the rest of Cog.
 */
class DelegatingEngine extends \Symfony\Component\Templating\DelegatingEngine
{

}