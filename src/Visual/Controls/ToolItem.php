<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\StringChar;

class ToolItem extends Control {

	public function getAllowedActions(): array {
		return [
			'click',
		];
	}

	public function getAvailableEvents(): array {
		return [
			'click',
		];
	}
	
	public function render(): string {
		$html = new StringChar('<input class="ToolItem" type="button" value="__value__" id="__objectId__" />');
		$html->replace('__value__',    $this->title)
			 ->replace('__objectId__', $this->getObjectID());

		return $html;
	}
}