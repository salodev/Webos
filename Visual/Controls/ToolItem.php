<?php
namespace Webos\Visual\Controls;
class ToolItem extends \Webos\Visual\Control {

	public function press() {
		$this->triggerEvent('press', array());
	}

	public function getAllowedActions() {
		return array(
			'press',
		);
	}

	public function getAvailableEvents() {
		return array(
			'press',
		);
	}
	
	public function render() {
		$html = new \Webos\String('<input class="ToolItem" type="button" value="__value__" id="__objectId__" />');
		$html->replace('__value__',    $this->title)
			 ->replace('__objectId__', $this->getObjectID());

		return $html;
	}
}