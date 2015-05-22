<?php

namespace Message\Cog\Filter\Exception;

/**
 * Class NoValueSetException
 * @package Message\Cog\Filter\Exception
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Exception to be thrown when no value is set on a filter before attempting to apply to a
 * query builder
 */
class NoValueSetException extends \LogicException
{

}