<?php

namespace Message\Cog\Serialization;

class ArrayToXml implements ArraySerializerInterface
{
	const DEFAULT_ROOT = 'xml';
	const PREFIX       = '<?xml version="1.0" encoding="UTF-8" ?>';

	private $_openingTag;
	private $_closingTag;

	private $_xml;

	public function serialize(array $data)
	{
		$this->_buildXML($data);

		if (count($data) !== 1 && (null === $this->_openingTag && null === $this->_closingTag)) {
			$this->setRoot(self::DEFAULT_ROOT);
		}

		$xml = self::PREFIX . $this->_openingTag . $this->_xml . $this->_closingTag;
		$this->_clear();

		return $xml;
	}

	public function deserialize($xml)
	{
		if (!is_string($xml) && !$xml instanceof \SimpleXMLElement) {
			throw new \InvalidArgumentException('XML must be either a string or an instance of \SimpleXMLElement');
		}

		if (!$xml instanceof \SimpleXMLElement) {
			if (!$this->_isValidXML($xml)) {
				throw new \LogicException('XML string is not valid');
			}
			$xml = new \SimpleXMLElement($xml);
		}

		$root = $xml->getName();

		return ($root === self::DEFAULT_ROOT) ? $this->_xmlToArray($xml) : [$root => $this->_xmlToArray($xml)];
	}

	public function setRoot($root)
	{
		if (!is_string($root)) {
			throw new \InvalidArgumentException('XML root must be a string');
		}

		$root = trim($root, '<>');
		$this->_openingTag = '<' . $root . '>';
		$this->_closingTag = '</' . $root . '>';
	}

	private function _buildXML(array $data)
	{
		if (!$this->_isAssoc($data)) {
			throw new \LogicException('Top level array must be associative!');
		}

		foreach ($data as $key => $value) {
			if (is_bool($value)) {
				$value = $value ? 'true' : 'false';
				$this->_xml .= $this->_addTags($value, $key);
			}
			elseif (is_array($value) && !$this->_isAssoc($value)) {
				foreach ($value as $val) {
					$this->_buildXML([$key => $val]);
				}
			}
			elseif (is_array($value)) {
				$this->_xml .= '<' . $key . '>';
				$this->_buildXML($value);
				$this->_xml .= '</' . $key . '>';
			}
			elseif (is_object($value)) {
				throw new \LogicException('Objects not currently supported by ArrayToXml serializer');
			}
			else {
				$this->_xml .= $this->_addTags($value, $key);
			}
		}
	}

	private function _addTags($value, $tag)
	{
		return '<' . $tag . '>' . $value . '</' . $tag . '>';
	}

	private function _xmlToArray(\SimpleXMLElement $xml)
	{
		$parsed = [];
		$xml = (array) $xml;

		foreach ($xml as $key => $value) {
			if ($value instanceof \SimpleXMLElement) {
				$parsed[$key] = $this->_xmlToArray($value);
			}
			elseif ($value === 'true') {
				$parsed[$key] = true;
			}
			elseif ($value === 'false') {
				$parsed[$key] = false;
			}
			elseif (is_numeric($value)) {
				$parsed[$key] = (floatval($value) == intval($value)) ? (int) $value : (float) $value;
			}
			else {
				$parsed[$key] = $value;
			}
		}

		return $parsed;
	}

	private function _isAssoc(array $data)
	{
		return array_values($data) !== $data;
	}

	private function _isValidXML($xml)
	{
		return (bool) @simplexml_load_string($xml);
	}

	private function _clear()
	{
		$this->_xml = null;
	}
}