<?php
namespace Webos\Visual\Controls;

use \Webos\ObjectsCollection;
use Webos\StringChar;

class ListBox extends Field {
	protected $_selectedItem = null;

	/**
	 * 
	 * @return \Webos\ObjectsCollection;
	 */
	public function items(): ObjectsCollection {
		return $this->getChildObjects();
	}

	public function setSelectedItem(ListItem $item): self {
		$this->_selectedItem = $item;

		$this->getApplication()->triggerSystemEvent('updateObject', $this, [
			'object' => $this,
		]);
		return $this;
	}

	public function getSelectedItem() {
		return $this->_selectedItem;
	}
	
	public function render(): string {
		$html = new StringChar(
			'<div class="ListFieldControl"__style__>' .
				'<div class="listWrapper">__content__</div>' .
			'</div>'
		);

		$html->replace('__style__', $this->getInlineStyle());

		$content = '';
		$content .= $this->items()->render();
		$html->replace('__content__', $content);
		return $html;
	}

}