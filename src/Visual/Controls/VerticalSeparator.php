<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;

class VerticalSeparator extends Control {
	public function initialize(array $params = []) {
		$this->top    = 0;
		$this->bottom = 0;
		$this->width  = 5;
		$this->left   = 200;
	}
	public function render(): string {
		$style = $this->getInlineStyle(true);
		return "<div class=\"resize-horizontal\" id=\"{$this->getObjectID()}\" webos drag-horizontal ondrag-horizontal {$style} >&nbsp;</div>";
	}
}