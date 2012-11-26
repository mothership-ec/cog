<?php

namespace Message\Cog\Templating;

/**
 * Empty wrapper around Symfony's `PhpEngine` class so we're not exposing any
 * Symfony code to the rest of Cog.
 *
 * This extends our own `EngineInterface` which can be used for type hinting.
 */
class PhpEngine extends \Symfony\Component\Templating\PhpEngine implements EngineInterface {

}