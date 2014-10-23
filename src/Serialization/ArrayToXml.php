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
		if (count($data) !== 1 && (null === $this->_openingTag && null === $this->_closingTag)) {
			$this->setRoot(self::DEFAULT_ROOT);
		}

		$this->_buildXML($data);

		$xml = self::PREFIX . $this->_openingTag . $this->_xml . $this->_closingTag;
		$this->_clear();

		return $xml;
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
		foreach ($data as $key => $value) {
			$key = (string) $key;
			$this->_xml .= '<' . $key . '>';

			if (is_bool($value)) {
				$this->_xml .= $value ? 'true' : 'false';
			}
			elseif (is_array($value)) {
				$this->_buildXML($value);
			}
			elseif (is_object($value)) {
				throw new \LogicException('Objects not currently supported by ArrayToXml serializer');
			}
			else {
				$this->_xml .= (string) $value;
			}

			$this->_xml .= '</' . $key . '>';
		}
	}

	private function _clear()
	{
		$this->_xml = null;
	}
}