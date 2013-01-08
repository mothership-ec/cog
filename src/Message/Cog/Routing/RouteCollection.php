<?php

namespace Message\Cog\Routing;

/**
 * Empty wrapper around Symfony's `RouteCollection` class so we're not exposing
 * any Symfony code to the rest of Cog.
 */
class RouteCollection extends \Symfony\Component\Routing\RouteCollection
{

}