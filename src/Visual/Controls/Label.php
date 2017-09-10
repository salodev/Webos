<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\StringChar;

class Label extends Control {
	public function render(): string {
		$html = new StringChar('<div class="LabelControl"__style__>__text__</div>');
		
		return $html
			->replace('__text__', ($this->text)??$this->value)
			->replace('__style__', $this->getInlineStyle());
	}
}