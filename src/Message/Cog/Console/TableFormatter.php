<?php

namespace Message\Cog\Console;

/**
*
*/
class TableFormatter
{
	protected $_headerStyle;
	protected $_headers = array();
	protected $_rows    = array();
	protected $_widths  = array();
	protected $_delimiter;

	public function __construct(array $headers = array(), array $rows = array(), $delimiter = "\t")
	{
		$this->setHeaders($headers);
		$this->setRows($rows);
		$this->_delimiter = $delimiter;
	}

	public function setHeaders($headers, $style = 'comment')
	{
		$this->_headers = $headers;
		$this->_headerStyle = $style;
	}

	public function setRows($rows)
	{
		$this->_rows = $rows;
	}

	public function addRow($row)
	{
		$this->_rows[] = $row;
	}

	public function write($output)
	{
		$data = $this->_detectColumnWidths();

		foreach($data as $rowNumber => $row) {
			$line = '';
			$delimiter = '';
			foreach($row as $colNumber => $column) {
				$text = str_pad($column, $this->_widths[$colNumber]);
				$line.= $delimiter.$text;
				$delimiter = $this->_delimiter;
			}

			if($rowNumber === 0) {
				$line = '<'.$this->_headerStyle.'>'.$line.'</'.$this->_headerStyle.'>';
			}

			$output->writeln($line);
		}
	}

	protected function _detectColumnWidths()
	{
		$data = array_merge(array($this->_headers), $this->_rows);

		for($i = 0; $i < count($data[0]); $i++) {
			$widest = 0;
			for($j = 0; $j < count($data); $j++) {
				$len = strlen($data[$j][$i]);
				if($len > $widest) {
					$widest = $len;
				}
			}
			$this->_widths[$i] = $widest;
		}

		return $data;
	}
}