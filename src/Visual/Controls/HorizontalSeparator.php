<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Exception;

class HorizontalSeparator extends Control {
	
	public function initialize(array $params = []) {
		$this->top    = 200;
		$this->height = 5;
		$this->left   = 0;
		$this->right  = 0;
		$this->enableEvent('drop');
	}
	
	public function __set_top($value) {
		$this->getPrevious()->top    = 0;
		$this->getPrevious()->bottom = null;
		$this->getPrevious()->height = $value;
		try {
			$this->getNext()->top    = $value + $this->height;
			$this->getNext()->bottom = 0;
			$this->getNext()->height = null;
		} catch (\Exception $e) {
			
		}
		return $value;
	}
	
	public function __set_bottom($value) {
		$this->getPrevious()->top    = 0;
		$this->getPrevious()->bottom = $value + $this->height;
		$this->getPrevious()->height = null;
		try {
			$this->getNext()->top    = null;
			$this->getNext()->bottom = 0;
			$this->getNext()->height = $value;
		} catch (\Exception $e) {
			
		}
		return $value;
	}
	
	public function getAllowedActions(): array {
		return array_merge(parent::getAllowedActions(), ['drop']);
	}
	
	public function drop($params) {
		if (!isset($params['top'])) {
			throw new Exception('Missing top parameter');
		}
		$this->top = $params['top']/1;
		$this->triggerEvent('drop', $params);
	}
	
	public function render(): string {
		$style = $this->getInlineStyle(true);
		return "<div class=\"resize-vertical\" id=\"{$this->getObjectID()}\" webos drag-vertical ondrag-vertical ondrop ignore-update-object=\"yes\" {$style} >&nbsp;</div>";
	}
}