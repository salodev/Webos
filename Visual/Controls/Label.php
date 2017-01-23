<?php
namespace Webos\Visual\Controls;

class Label extends \Webos\Visual\Control {
	public function render() {
		$html = new \Webos\String('<div class="LabelControl"__style__>__text__</div>');
		
		return $html
			->replace('__text__', ($this->text)?$this->text:$this->value)
			->replace('__style__', $this->getInlineStyle());
	}
}