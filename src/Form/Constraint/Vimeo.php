<?php

namespace Message\Cog\Form\Constraint;

use Symfony\Component\Validator;

/**
 * Class Vimeo
 * @package Message\Cog\Form\Constraint
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *          
 * Constraint for validating Vimeo URLs in forms
 */
class Vimeo extends Validator\Constraints\Url
{

}