<?php

namespace Message\Cog\Filter;

use Message\Cog\DB\QueryBuilderInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Interface FilterInterface
 * @package Message\Cog\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Interface representing a filter. A filter is a class that can alter a query based on
 * user input via a form.
 */
interface FilterInterface
{
	/**
	 * Get a string identifier for the filter
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get a translation string for a human readable version of the name of the filter
	 *
	 * @return string
	 */
	public function getDisplayName();

	/**
	 * Override default options set against the form
	 *
	 * @param array $options
	 */
	public function setOptions(array $options);

	/**
	 * Get the options for the form
	 *
	 * @return array
	 */
	public function getOptions();

	/**
	 * Get the form field that the filter will be represented by, either as an instance of AbstractType
	 * or as a string representation of the form field.
	 *
	 * @return string | AbstractType
	 */
	public function getForm();

	/**
	 * Set the value for the filter as supplied by the form
	 *
	 * @param $value
	 */
	public function setValue($value);

	/**
	 * Apply the filters to the query taken from the loader
	 *
	 * @param QueryBuilderInterface $queryBuilder
	 */
	public function apply(QueryBuilderInterface $queryBuilder);
}