<?php

namespace Message\Cog\Routing;

/**
 * Empty wrapper around Symfony's `UrlGenerator` class so we're not exposing
 * any Symfony code to the rest of Cog.
 */
class UrlGenerator extends \Symfony\Component\Routing\Generator\UrlGenerator
{
}