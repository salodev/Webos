<?php

namespace Webos\FrontEnd\CSS;

class Definition {
	
	private $_rules = [];
	public function __construct(array $rules = []) {
		$this->_rules = $rules;
	}
	
	public function getArray(): array {
		return $this->_rules;
	}
	
	public function invert(): self {
		$background = $this->_rules['color'];
		$color      = $this->_rules['background'];
		$this->_rules['color'] = $color;
		$this->_rules['background'] = $background;
		return $this;
	}
	
	public function getInverted(): self {
		$newRule = clone $this;
		$newRule->invert();
		return $newRule;
	}
	
	public function import(string ...$defNames): self {
		foreach($defNames as $defName) {
			$rule = StylesManager::Instance()->getDefinition($defName);
			$this->_rules = array_merge($this->_rules, $rule->getArray());
		}
		return $this;
	}
	
	public function like(string ...$ruleNames): self {
		foreach($ruleNames as $ruleName) {
			$rule = StylesManager::Instance()->getRule($ruleName);
			$this->_rules = array_merge($this->_rules, $rule->getArray());
		}
		return $this;
	}
	
	public function important($name): self {
		$this->_rules[$name] .= ' !important';
		return $this;
	}
}
