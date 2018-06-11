<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;

class VerticalSeparator extends Control {
	public function initialize(array $params = []) {
		$this->top    = 0;
		$this->bottom = 0;
		$this->width  = 5;
		// $this->left   = 200;
		$this->enableEvent('drop');
	}
	
	public function __set_left($value) {
		$this->getPrevious()->left = 0;
		$this->getPrevious()->width = $value;
		try {
			$this->getNext()->right = 0;
			$this->getNext()->left = $value + $this->width;
			$this->getNext()->width = null;
		} catch (\Exception $e) {
			
		}
		return $value;
	}
	
	public function __set_right($value) {
		$this->getPrevious()->left = 0;
		$this->getPrevious()->right = $value + $this->width;
		try {
			$this->getNext()->right = 0;
			$this->getNext()->width = $value;
			$this->getNext()->left  = null;
		} catch (\Exception $e) {
			
		}
		return $value;
	}
	
	public function getAllowedActions(): array {
		return array_merge(parent::getAllowedActions(), ['drop']);
	}
	
	public function drop($params) {
		if (!isset($params['left'])) {
			throw new Exception('Missing top parameter');
		}
		$this->left = $params['left']/1;
		$this->triggerEvent('drop', $params);
	}
	
	public function render(): string {
		$style = $this->getInlineStyle(true);
		return "<div class=\"resize-horizontal\" id=\"{$this->getObjectID()}\" webos drag-horizontal ondrag-horizontal ondrop ignore-update-object=\"yes\" {$style} >&nbsp;</div>";
	}
}