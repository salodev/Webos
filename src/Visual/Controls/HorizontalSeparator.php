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
	
	public function action_drop(array $params = []): void {
		if (!$this->draggable) {
			return;
		}
		if (!isset($params['top'])) {
			throw new Exception('Missing top parameter');
		}
		$this->top = $params['top']/1;
		$this->triggerEvent('drop', $params);
	}
	
	public function render(): string {
		$style      = $this->getInlineStyle(true);
		$directives = $this->draggable ? 'webos drag-vertical ondrag-vertical ondrop ignore-update-object="yes"' : '';
		$class      = $this->draggable ? 'resize-vertical' : '';
		return "<div class=\"{$class}\" id=\"{$this->getObjectID()}\" {$directives} {$style} >&nbsp;</div>";
	}
}