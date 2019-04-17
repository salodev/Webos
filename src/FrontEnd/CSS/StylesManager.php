<?php
namespace Webos\FrontEnd\CSS;
use Exception;

class StylesManager {
	static private $_instance;
	private $_definitions = [];
	private $_rules = [];
	private $_palettes = [];
	
	static public function Instance(): self {
		if (!self::$_instance) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}
	
	public function define(string $name, array $attributes = []): Definition {
		$rule = new Definition($attributes);
		$this->_definitions[$name] = $rule;
		return $rule;
	}
	
	public function defineColor(string $name, string $fgColor, string $bgColor = '#ffffff'): Definition {
		$this->addPalette($name, $fgColor);
		return $this->define($name, [
			'color'      => $this->getPalette($name),
			'background' => $bgColor,
		]);
	}
	
	public function getDefinition(string $name): Definition {
		if (!array_key_exists($name, $this->_definitions)) {
			throw new Exception('Definition not found');
		}
		return $this->_definitions[$name];
	}
	
	public function addRule(string $name, array $attributes = []): Definition {
		$rule = new Definition($attributes);
		$this->_rules[$name] = $rule;
		return $rule;
	}
	
	public function getRule(string $name): Definition {
		if (!array_key_exists($name, $this->_rules)) {
			throw new Exception('Rule not found');
		}
		return $this->_rules[$name];
	}
	
	public function addPalette(string $name, string $color): self {
		$this->_palettes[$name] = $color;
		return $this;
	}
	
	public function palette(string $name): string {
		return $this->getPalette($name);
	}
	
	public function getPalette(string $name): string {
		if (!array_key_exists($name, $this->_palettes)) {
			throw new Exception('Palette not found');
		}
		return $this->_palettes[$name];
	}
	
	public function getStyles($compressed = true): string {
		$str = '';
		foreach($this->_rules as $name => $rule) {
			$str .= "{$name} { " . ($compressed?'':"\n");
			$styles = $rule->getArray();
			foreach($styles as $sName => $sValue) {
				$str .= $compressed?"{$sName}:{$sValue}; ":"\t{$sName}: {$sValue};\n";
			}
			$str .= "}\n";
		}
		return $str;
	}
}
