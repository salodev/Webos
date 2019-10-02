<?php

namespace Webos\Visual\Controls;

class HtmlContainerNode extends HtmlContainer {
		
	public function render(): string {
		$content = $this->getChildObjects()->render();
		$clickDirective = $this->hasListenerFor('click') ? 'click' : '';
		$styles = $this->getInlineStyle(true);
		return "<{$this->tagName} id=\"{$this->getObjectID()}\" webos {$clickDirective} href=\"\" {$styles}>{$this->text}{$content}</{$this->tagName}>" ;
	}
}