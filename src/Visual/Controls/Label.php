<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\StringChar;

class Label extends Control {
	public function render(): string {
		$html = new StringChar('<div id="__ID__" class="LabelControl"__style__>__text__</div>');
		
		return $html
			->replace('__ID__',    $this->getObjectID())
			->replace('__text__',  $this->value??$this->text)
			->replace('__style__', $this->getInlineStyle());
	}
}