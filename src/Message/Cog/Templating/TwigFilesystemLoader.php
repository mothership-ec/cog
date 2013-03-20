<?php

namespace Message\Cog\Templating;

use Twig_Loader_Filesystem;

use InvalidArgumentException;

/**
 * Custom filesystem loader for Twig view files.
 *
 * We have extended the default Twig filesystem loader because we need to
 * intercept references to parse them into full file paths before they are
 * parsed. This basically means we can use Cog references in Twig files for
 * extending views and so on.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class TwigFilesystemLoader extends Twig_Loader_Filesystem
{
	protected $_parser;

	/**
	 * Constructor.
	 *
	 * @param string|array   $paths  Path or paths to look for views in
	 * @param ViewNameParser $parser Our view name parser, for parsing references
	 */
	public function __construct($paths, ViewNameParser $parser)
	{
		$this->_parser = $parser;

		parent::__construct($paths);
	}

	/**
	 * @see Twig_Loader_Filesystem
	 *
	 * @throws InvalidArgumentException If the parsed view name is not a Twig file
	 */
	public function findTemplate($name)
	{
		$parsed = $this->_parser->parse($name);

		if ('twig' !== $parsed->get('engine')) {
			throw new InvalidArgumentException(sprintf(
				'View `%s` is not a Twig file, and as such cannot be parsed by Twig.',
				$name
			));
		}

		return parent::findTemplate($parsed->getPath());
	}
}