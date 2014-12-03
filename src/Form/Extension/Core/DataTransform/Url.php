<?php

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class Url
 * @package Message\Mothership\CMS\Form\DataTransform
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * DataTransformer to append 'http://' protocol to URLs that do not have a protocol
 */
class Url implements DataTransformerInterface
{
	/**
	 * Do not transform URL while going into form
	 *
	 * @param string $url
	 * @return string
	 */
	public function transform($url)
	{
		return (string) $url;
	}

	/**
	 * Append 'http://' protocol to URL strings that do not have it
	 *
	 * @param string $url
	 * @throws \Symfony\Component\Form\Exception\TransformationFailedException
	 *
	 * @return null|string
	 */
	public function reverseTransform($url)
	{
		if (null === $url) {
			return null;
		}

		if (!is_string($url)) {
			throw new TransformationFailedException('URL could not be converted to a string');
		}

		if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
			return "http://" . $url;
		}

		return $url;
	}
}