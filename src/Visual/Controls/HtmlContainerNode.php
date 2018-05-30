<?php

namespace Webos\Visual\Controls;

use Webos\Visual\Control;

class HtmlContainerNode extends Control {
		
	public function render(): string {
		$content = $this->getChildObjects()->render();
		return "<{$this->tagName} id=\"{$this->getObjectID()}\" webos click href=\"\">{$this->text}{$content}</{$this->tagName}>" ;
	}
	
	public function getAvailableEvents(): array {
		return array_merge(parent::getAvailableEvents(), ['click']);
	}
	
	public function getAllowedActions(): array {
		return array_merge(parent::getAllowedActions(), ['click']);
	}
	
	public function click() {
		$this->triggerEvent('click');
	}
}