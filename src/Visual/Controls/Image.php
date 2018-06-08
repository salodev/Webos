<?php

namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Exception;
use Webos\StringChar;

class Image extends Control {
	
	public function initialize(array $params = []): void {
	}
	
	public function render(): string {
		$url = $this->url;
		$html = "<img id=\"{$this->getObjectID()}\" {$this->getInlineStyle()} src=\"{$url}\" />";
		return $html;
	} 
}