<?php

namespace Message\Cog\Templating;

/**
 * Empty wrapper around Symfony's `EngineInterface` engine so we're not exposing
 * any Symfony code to the rest of Cog.
 */
interface EngineInterface extends \Symfony\Component\Templating\EngineInterface
{

}