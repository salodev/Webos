<?php
namespace Webos\Visual\Controls\DataTable;

class Column {
	public $label;
	public $fieldName;
	public $width         = 100;
	public $allowOrder    = false;
	public $linkable      = false;
	public $align         = 'left';
	public $format        = null;
	public $decimals      = null;
	public $decimalsGlue  = null;
	public $thousandsGlue = null;
	public $dateFormat    = null;
	
	public function __construct($label, $fieldName) {
		$this->label     = $label;
		$this->fieldName = $fieldName;
	}
	
	public function width($value) {
		$this->width = $value;
		return $this;
	}
	
	public function allowOrder($value) {
		$this->allowOrder = $value;
		return $this;
	}
	
	public function linkable($value) {
		$this->linkable = $value;
		return $this;
	}
	
	public function align($value) {
		$this->align = $value;
		return $this;
	}
	
	public function format($value) {
		$this->format = $value;
		return $this;
	}
	
	public function decimal($decimals = 2, $decimalsGlue = '.', $thousandsGlue = '') {
		$this->decimals = $decimals;
		$this->decimalsGlue = $decimalsGlue;
		$this->thousandsGlue = $thousandsGlue;
	}
	
	public function dateFormat($format) {
		$this->dateFormat = $format;
	}
	
	public function renderValue($value) {
		if ($this->decimals !== null) {
			if (is_numeric($value)) {
				return number_format($value/1, $this->decimals, $this->decimalsGlue, $this->thousandsGlue);
			}
			return $value;
		}
		
		if ($this->format !== null) {
			return sprintf($this->format, $value);
		}
		
		if ($this->dateFormat !== null) {
			list($m, $d, $y) = explode('-', date('m-d-Y', strtotime($value)));
			if (checkdate($m/1, $d/1, $y/1)) {
				return date($this->dateFormat, $value);
			}
		}
		
		return $value;
	}
}