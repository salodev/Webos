<?php
namespace Webos\Visual\Controls;
class ToolItem extends \Webos\Visual\Control {

	public function click() {
		$this->triggerEvent('click', array());
	}

	public function getAllowedActions(): array {
		return array(
			'click',
		);
	}

	public function getAvailableEvents(): array {
		return array(
			'click',
		);
	}
	
	public function render(): string {
		$html = new \Webos\StringChar('<input class="ToolItem" type="button" value="__value__" id="__objectId__" />');
		$html->replace('__value__',    $this->title)
			 ->replace('__objectId__', $this->getObjectID());

		return $html;
	}
}