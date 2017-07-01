<?php

namespace Webos\Visual\Controls;
class ListItem extends \Webos\Visual\Control {

	public function getAllowedActions() {
		return array(
			'press'
		);
	}

	public function getAvailableEvents() {
		return array(
			'press'
		);
	}

	public function press() {
		$this->getParent()->setSelectedItem($this);
		$this->triggerEvent('press');
	}
	
	public function render() {
		$html = new \Webos\String(
			'<div class="ItemListFieldControl__selected__" onclick="__onclick__">' .
			'__title__' .
			'</div>'
		);
		
		$onclick = new \Webos\String(
			"location.href='index.php?actionName=press&objectId=" .
			$this->getObjectID() . "';"
		);

		$parent = $this->getParent()->getSelectedItem();
		
		if ($parent && $parent->getObjectID()==$this->getObjectID()) {
			$selected = ' selected';
		} else {
			$selected = '';
		}

		$html
			->replace('__onclick__', $onclick)
			->replace('__title__', $this->title)
			->replace('__selected__', $selected);

		return $html;
	}
}
