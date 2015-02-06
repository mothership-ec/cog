<?php

namespace Message\Cog\AssetManagement;

use Message\Cog\Module\ReferenceParserInterface;

use Assetic\Asset\FileAsset;
use Assetic\Util\VarUtils;

/**
 * Asset for a file using a Cog reference. The reference is parsed before it is
 * passed on to the base FileAsset class.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class FileReferenceAsset extends FileAsset
{
	/**
	 * {@inheritDoc}
	 *
	 * @param ReferenceParserInterface $parser The reference parser
	 * @param string                   $source     An absolute path
	 * @param array                    $filters    An array of filters
	 * @param string                   $sourceRoot The source asset root directory
	 * @param string                   $sourcePath The source asset path
	 * @param array                    $vars
	 *
	 * @throws \InvalidArgumentException If the supplied root doesn't match the
	 *                                   source when guessing the path
	 */
	public function __construct(ReferenceParserInterface $parser, $source, $filters = array(), $sourceRoot = null, $sourcePath = null, array $vars = array())
	{
		$source = $parser->parse($source)->getFullPath();

		parent::__construct($source, $filters, $sourceRoot, $sourcePath, $vars);
	}
}