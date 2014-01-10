<?php

namespace Message\Cog\Templating;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Module\ReferenceParserInterface;
use Message\Cog\Filesystem\Finder;

use Symfony\Component\Templating\TemplateReference;
use Symfony\Component\Templating\TemplateNameParser;

use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class ViewNameParser extends TemplateNameParser
{
	protected $_parser;
	protected $_fileTypes;
	protected $_formats;

	protected $_lastAbsoluteModule;
	protected $_defaultDirs = array();

	/**
	 * Constructor.
	 *
	 * @param ReferenceParserInterface $parser    Reference parser class
	 * @param array                    $fileTypes Array of filetypes to support, in order of preference
	 * @param array                    $fileTypes Array of formats to support, in order of preference
	 */
	public function __construct(ReferenceParserInterface $parser, Finder $finder, array $fileTypes, array $formats)
	{
		$this->_parser    = $parser;
		$this->_finder    = $finder;
		$this->_fileTypes = $fileTypes;
		$this->_formats   = $formats;
	}

	/**
	 * Add a default directory to use for searching for view overrides before
	 * looking in the parsed location.
	 *
	 * Given a view in cogule Message\Mothership\CMS, the search directory will
	 * be set to: [defaultDir]/Message:Mothership:CMS/[parsedViewPath].
	 *
	 * @param string $dir The directory to look within
	 */
	public function addDefaultDirectory($dir)
	{
		$this->_defaultDirs[] = rtrim($dir, '/') . '/';
	}

	/**
	 * Parses a view reference & determines which view file to use.
	 *
	 * Looks at the allowed content types for the current request and checks,
	 * for each, if a view file exists (for each engine defined in
	 * $this->_fileTypes in order of priority). As soon as it finds a view that
	 * exists, it returns this.
	 *
	 * @param string $reference  The view reference (without the format)
	 * @param bool   $batch      Return a batch of templates
	 *
	 * @return string            The view file path
	 *
	 * @throws NotAcceptableHttpException If the view format could not be determined
	 *
	 * @todo What if there's no request object?
	 * @todo Notify the response of the chosen response type
	 */
	public function parse($reference, $batch = false)
	{
		// Return if it's already been parsed
		if ($reference instanceof TemplateReference) {
			return $reference;
		}

		$parsed = $this->_parser->parse($reference);

		$referenceSeparator = constant(get_class($this->_parser) . '::SEPARATOR');

		// If it is relative and an absolute path was used previously, make the
		// reference absolute using the previous module name
		// This is a fix for https://github.com/messagedigital/cog/issues/40
		// which should be improved/refactored at a later date
		if ($parsed->isRelative() && $this->_lastAbsoluteModule) {
			// If it is relative, make it absolute with the last module name
			$newReference = str_replace('\\', $referenceSeparator, $this->_lastAbsoluteModule) . $reference;

			// Parse the new reference
			$parsed = $this->_parser->parse($newReference);
		}
		else if (!$parsed->isRelative()) {
			$this->_lastAbsoluteModule = $parsed->getModuleName();
		}

		// Force the parser to not look in the library
		$parsed->setInLibrary(false);

		// If parsing a batch, return an array of templates
		$templates = array();

		// Set default check paths
		$checkPaths = array();
		foreach ($this->_defaultDirs as $dir) {
			$checkPaths[] = $dir
				. str_replace('\\', $referenceSeparator, $parsed->getModuleName())
				. '/'
				. $parsed->getPath();
		}

		// Get the base file name from the reference parser
		$checkPaths[] = $parsed->getFullPath('resources/view');

		// Loop paths to check, returning on the first one to match
		foreach ($checkPaths as $baseFileName) {
			// Loop through each content type
			foreach ($this->_formats as $format) {
				// Loop through the engines in order of preference
				foreach ($this->_fileTypes as $engine) {
					// Check if a view file exists for this format and this engine
					$fileName = $baseFileName . '.' . $format . '.' . $engine;
					if (file_exists($fileName)) {
						$template = new TemplateReference($fileName, $engine);

						if (!$batch) {
							return $template;
						}

						// If override doesn't exist, set the original view
						if (!array_key_exists($format, $templates)) {
							$templates[$format] = $template;
						}
					}

				}
			}
		}

		if (count($templates) > 0) {
			return $templates;
		}

		throw new NotAcceptableHttpException(sprintf(
			'View format could not be determined for reference `%s`',
			$reference
		));
	}

	/**
	 * Get the absolute path for a parsed reference.
	 *
	 * @param  ReferenceParser $parsed Parsed reference
	 * @return ReferenceParser         Absolute parsed reference
	 */
	public function getAbsolute($reference, $parsed)
	{
		// If it is relative and an absolute path was used previously, make the
		// reference absolute using the previous module name
		// This is a fix for https://github.com/messagedigital/cog/issues/40
		// which should be improved/refactored at a later date
		if ($parsed->isRelative() && $this->_lastAbsoluteModule) {
			// If it is relative, make it absolute with the last module name
			$referenceSeparator = constant(get_class($this->_parser) . '::SEPARATOR');
			$newReference = str_replace('\\', $referenceSeparator, $this->_lastAbsoluteModule) . $reference;

			// Parse the new reference
			$parsed = $this->_parser->parse($newReference);
		}
		else if (!$parsed->isRelative()) {
			$this->_lastAbsoluteModule = $parsed->getModuleName();
		}

		return $parsed;
	}


}