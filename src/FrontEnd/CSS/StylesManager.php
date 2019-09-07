<?php
namespace Webos\FrontEnd\CSS;
use Exception;

class StylesManager {
	static private $_instance;
	private $_definitions = [];
	private $_rules = [];
	private $_palettes = [];
	
	static public function Instance(): self {
		if (!static::$_instance) {
			static::$_instance = new static;
		}
		
		return static::$_instance;
	}
	
	static public function InstanceWithDefinitions():self {
		$sm = static::Instance();
		static::setInitialDefinitions($sm);
		return $sm;
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
	
	static public function setInitialDefinitions(self $sm) {
		$sm->defineColor('yellow',   '#f5b400');
		$sm->defineColor('lightgray',   '#f1f3f4');
		
		// $sm->defineColor('blue',     '#5589e1');
		// $sm->defineColor('blue',     '#4a78c5');
		$sm->defineColor('blue',     'rgba(74,120,197,0.95)');
		$sm->defineColor('red',      '#dc4437');
		$sm->defineColor('green',    '#56a845');
		$sm->defineColor('paper',    '#000000', '#ffffff');
		$sm->defineColor('gray',     '#cdcdcd', '#ffffff');
		$sm->defineColor('gray1',    '#eeeeee', '#ffffff');
		$sm->defineColor('gray2',    '#dbe2e5', '#ffffff');
		$sm->defineColor('gray3',    '#5C5F69', '#ffffff');
		$sm->defineColor('gray4',    '#555555', '#ffffff');
		$sm->defineColor('gray5',    '#28292d', '#ffffff');
		$sm->defineColor('darkgray', '#666666', '#ffffff');
		$sm->addPalette('white', '#ffffff');
		$sm->addPalette('black', '#000000');
		
		$sm->define('noborder', [
			'border' => 'none',
		]);

		$sm->define('box', [
			'padding' => '3px 9px',
		]);

		$sm->define('click', [
			'cursor' => 'pointer',
		]);

		$sm->define('control', [
			'border' => 'none',
			// 'border-top' => 'none',
			// 'border-left' => 'none',
			// 'border-right' => 'none',
			// 'border-bottom' => 'solid 1px ' . $sm->getPalette('gray'),
			'background' => $sm->getPalette('lightgray'),
		])->import('box');
		
		$sm->define('hover', [
			'background' => $sm->getPalette('gray2') . ' !important',
		]);

		$sm->define('icon', [
			'font-family' => 'Glyphicons Halflings',
		]);
		$sm->define('icon-chevron-down', [
			'content' => '"\E114"',
		])->import('icon');
	}
}
