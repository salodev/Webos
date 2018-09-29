<?php

namespace Webos\Visual\BootstrapUI;
use Webos\StringChar;

class Bar extends Control {
	public function render(): string {
		$html = new StringChar();
		$html->concat('<nav ');
		$html->concat('id="__ID__" ');
		$html->concat('class="navbar navbar-expand-lg navbar-dark bg-dark">');
		
		if ($this->title) {
			$html->concat("<a class=\"navbar-brand\" href=\"#\">{$this->title}</a>");
		}
		
		$html->concat('__CONTENT__');
		$html->concat('</nav>');
		
		$html->replace('__ID__', $this->getObjectID());
		$html->replace('__CONTENT__', $this->getChildObjects()->render());
		return $html;
	}
	
	public function setTitle(string $value): self {
		$this->title = $value;
		return $this;
	}
}
