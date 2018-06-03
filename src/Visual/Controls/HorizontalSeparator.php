<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;

class HorizontalSeparator extends Control {
	
	public function initialize(array $params = []) {
		$this->top    = 200;
		$this->height = 5;
		$this->left   = 0;
		$this->right  = 0;
	}
	public function render(): string {
		$style = $this->getInlineStyle(true);
		return "<div class=\"resize-vertical\" id=\"{$this->getObjectID()}\" webos drag-vertical ondrag-vertical {$style} >&nbsp;</div>";
	}
}