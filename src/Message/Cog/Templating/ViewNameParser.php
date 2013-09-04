<?php

namespace Message\Cog\Templating;

use Message\Cog\Service\ContainerInterface;
use Message\Cog\Module\ReferenceParserInterface;

use Symfony\Component\Templating\TemplateReference;
use Symfony\Component\Templating\TemplateNameParser;

use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class ViewNameParser extends TemplateNameParser
{
	protected $_services;
	protected $_parser;
	protected $_fileTypes;
	protected $_formats;

	protected $_lastAbsoluteModule;

	/**
	 * Constructor.
	 *
	 * @param ContainerInterface       $services  The service container
	 * @param ReferenceParserInterface $parser    Reference parser class
	 * @param array                    $fileTypes Array of filetypes to support, in order of preference
	 * @param array                    $fileTypes Array of formats to support, in order of preference
	 */
	public function __construct(ContainerInterface $services, ReferenceParserInterface $parser, array $fileTypes, array $formats)
	{
		$this->_services  = $services;
		$this->_parser    = $parser;
		$this->_fileTypes = $fileTypes;
		$this->_formats = $formats;
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
	 * @param string $batch      Return a batch of templates
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
		// No need to parse multiple times.
		if($reference instanceof TemplateReference) {
			return $reference;
		}

		$parsed  = $this->_parser->parse($reference);

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

		// Force the parser to not look in the library
		$parsed->setInLibrary(false);

		// Get the base file name from the reference parser
		$baseFileName = $parsed->getFullPath('resources/view');

		// If parsing a batch, return an array of templates
		$templates = array();

		// Loop through each content type
		foreach ($this->_formats as $format) {
			// Loop through the engines in order of preference
			foreach ($this->_fileTypes as $engine) {
				// Check if a view file exists for this format and this engine
				$fileName = $baseFileName . '.' . $format . '.' . $engine;
				if (file_exists($fileName)) {

					$template = new TemplateReference($fileName, $engine);

					if(!$batch) {
						return $template;
					}

					$templates[$format] = $template;
				}
			}
		}

		if($templates) {
			return $templates;
		}

		throw new NotAcceptableHttpException(sprintf(
			'View format could not be determined for reference `%s`',
			$reference
		));
	}
}