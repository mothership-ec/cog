<?php

namespace Message\Cog\HTTP;

use Symfony\Component\HttpFoundation\RedirectResponse as BaseRedirectResponse;

/**
 * @see \Symfony\Component\HttpFoundation\RedirectResponse
 */
class RedirectResponse extends BaseRedirectResponse implements ResponseInterface
{

}