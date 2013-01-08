<?php

namespace Message\Cog\Routing;

/**
 * Empty wrapper around Symfony's `RouterInterface` interface so we're not
 * exposing any Symfony code to the rest of Cog.
 */
interface RouterInterface extends \Symfony\Component\Routing\RouterInterface
{

}