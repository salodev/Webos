<?php
namespace Webos\Visual\Controls;

class ListBox extends Field {
	protected $_selectedItem = null;

	/**
	 * 
	 * @return \Webos\ObjectsCollection;
	 */
	public function items() {
		return $this->getChildObjects();
	}

	public function setSelectedItem(ListItem $item) {
		$this->_selectedItem = $item;

		$this->getParentApp()->triggerSystemEvent('updateObject', $this, array(
			'object' => $this,
		));

	}

	public function getSelectedItem() {
		return $this->_selectedItem;
	}
	
	public function render() {
		$html = new \Webos\String(
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