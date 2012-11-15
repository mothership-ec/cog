<?php

namespace Message\Cog;

/**
 * Interface for reference parser class.
 *
 * The reference parser is used in the routing and templating components to
 * identify files/classes with a short reference.
 *
 * @author Joe Holdcroft <joe@message.uk.com>
 */
interface ReferenceParserInterface
{
	public function getSymfonyLogicalControllerName();

	public function getFullPath($pathNamespace = null);

	public function getPath($pathNamespace = null, $separator = null);

	public function getClassName($pathNamespace);

	public function getAllParts();

	public function parse($reference);
}