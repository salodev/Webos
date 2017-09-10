<?php

namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\StringChar;

class ListItem extends Control {

	public function getAllowedActions(): array {
		return array(
			'click'
		);
	}

	public function getAvailableEvents(): array {
		return array(
			'click'
		);
	}

	public function click() {
		$this->getParent()->setSelectedItem($this);
		$this->triggerEvent('click');
	}
	
	public function render(): string {
		$html = new StringChar(
			'<div class="ItemListFieldControl__selected__" webos click>' .
			'__title__' .
			'</div>'
		);
		
		$parent = $this->getParent()->getSelectedItem();
		
		if ($parent && $parent->getObjectID()==$this->getObjectID()) {
			$selected = ' selected';
		} else {
			$selected = '';
		}

		$html
			->replace('__title__', $this->title)
			->replace('__selected__', $selected);

		return $html;
	}
}
