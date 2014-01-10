<?php

namespace Message\Cog\Routing;

/**
 * Empty wrapper around Symfony's `RequestContext` class so we're not exposing
 * any Symfony code to the rest of Cog.
 */
class RequestContext extends \Symfony\Component\Routing\RequestContext
{

}