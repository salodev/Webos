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
	
	public function getAllowedActions(): array {
		return array_merge(parent::getAllowedActions(), ['drop']);
	}
	
	public function drop($params) {
		if (!isset($params['top'])) {
			throw new Exception('Missing top parameter');
		}
		$this->top = $params['top']/1;
		$this->getPrevious()->height = $this->top;
		$this->getNext()->top = $this->top + $this->height;
		$this->triggerEvent('drop', $params);
	}
	
	public function render(): string {
		$style = $this->getInlineStyle(true);
		return "<div class=\"resize-vertical\" id=\"{$this->getObjectID()}\" webos drag-vertical ondrag-vertical ondrop ignore-update-object=\"yes\" {$style} >&nbsp;</div>";
	}
}