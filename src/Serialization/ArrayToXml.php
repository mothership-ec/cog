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
		try {
			$root = $this->_setRootFromData($data);

			$data = (null !== $root) ? $data[$root] : $data;

			$xml = self::PREFIX . $this->_openingTag . $this->_closingTag;
			$xml = new \SimpleXMLElement($xml);

			$xml = $this->_buildXML($data, $xml)->asXML();

			$this->_clear();

			return $xml;
		}
		catch (\Exception $e) {
			throw new SerializationException('Could not serialize data: ' . $e->getMessage() . ',  line ' . $e->getLine());
		}
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

	private function _setRootFromData(array $data)
	{
		if (count($data) !== 1 && !$this->_rootSet()) {
			$this->setRoot(self::DEFAULT_ROOT);
			return null;
		}
		elseif (!$this->_rootSet()) {
			reset($data);
			$root = key($data);
			$this->setRoot($root);
			return $root;
		}

		return null;
	}

	private function _buildXML(array $data, &$xml)
	{
		foreach($data as $key => $value) {
			if(is_array($value)) {
				if(!is_numeric($key)){
					$sub = $xml->addChild("$key");
					$this->_buildXML($value, $sub);
				}
				else{
					$this->_buildXML($value, $xml);
				}
			}
			else {
				$xml->addChild("$key",htmlspecialchars("$value"));
			}
		}

		return $xml;
	}

	private function _xmlToArray($xml)
	{
		if (!is_array($xml) && !$xml instanceof \SimpleXMLElement) {
			return $this->_parseXMLValue($xml);
		}

		$parsed = [];
		$xml = (array) $xml;

		foreach ($xml as $key => $value) {
			$parsed[$key] = $this->_parseXMLValue($value);
		}

		return $parsed;
	}

	private function _parseXMLValue($value)
	{
		if ($value instanceof \SimpleXMLElement) {
			return $this->_xmlToArray($value);
		}
		elseif (is_array($value)) {
			$new = [];
			foreach ($value as $k => $v) {
				$new[$k] = $this->_xmlToArray($v);
			}
			return $new;
		}
		elseif ($value === 'true') {
			return true;
		}
		elseif ($value === 'false') {
			return false;
		}
		elseif (is_numeric($value)) {
			return (floatval($value) == intval($value)) ? (int) $value : (float) $value;
		}
		else {
			return $value;
		}
	}

	private function _isValidXML($xml)
	{
		return (bool) @simplexml_load_string($xml);
	}

	private function _clear()
	{
		$this->_xml = null;
		$this->_openingTag = null;
		$this->_closingTag = null;
	}

	private function _rootSet()
	{
		return (null !== $this->_openingTag && null !== $this->_closingTag);
	}
}