<?php
namespace Webos\Visual\Controls\DataTable;

use Webos\Closure;

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
	public $renderFn      = null;
	
	public function __construct(string $label, string $fieldName) {
		$this->label     = $label;
		$this->fieldName = $fieldName;
	}
	
	public function title(string $value): self {
		$this->label = $value;
		return $this;
	}
	
	public function label(string $value): self {
		$this->label = $value;
		return $this;
	}
	
	public function name(string $value): self {
		$this->fieldName = $value;
		return $this;
	}
	
	public function fieldName(string $value): self {
		return $this->name($value);
	}
	
	public function width(int $value): self {
		$this->width = $value;
		return $this;
	}
	
	public function allowOrder(bool $value): self {
		$this->allowOrder = $value;
		return $this;
	}
	
	public function linkable(bool $value): self {
		$this->linkable = $value;
		return $this;
	}
	
	public function align(string $value): self {
		$this->align = $value;
		return $this;
	}
	
	public function format(string $value): self {
		$this->format = $value;
		return $this;
	}
	
	public function decimal(int $decimals = 2, string $decimalsGlue = '.', string $thousandsGlue = ''): self {
		$this->decimals = $decimals;
		$this->decimalsGlue = $decimalsGlue;
		$this->thousandsGlue = $thousandsGlue;
		$this->align = 'right';
		return $this;
	}
	
	public function dateFormat(string $format): self {
		$this->dateFormat = $format;
		return $this;
	}
	
	public function setRenderFn(callable $fn) {
		$this->renderFn = new Closure($fn);
	}
	
	public function renderValue($value, array $row = []) {
		
		if ($this->renderFn instanceof Closure) {
			$fn = $this->renderFn;
			return $fn($value, $row);
		}
		
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
			if (is_numeric($value)) {
				return date($this->dateFormat, $value);;
			}
			list($m, $d, $y) = explode('-', date('m-d-Y', strtotime($value)));
			if (checkdate($m/1, $d/1, $y/1)) {
				return date($this->dateFormat, $value);
			}
		}
		
		return $value;
	}
}