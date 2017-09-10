<?php
namespace Webos\Visual\Controls\Menu;

use Webos\Visual\Control;
use Webos\StringChar;

class ListItems extends Control {

	protected $_selectedItem = null;

	public function  getAvailableEvents(): array {
		return array();
	}

	public function  getAllowedActions(): array {
		return array();
	}

	public function getSelectedItem(): Item {
		return $this->_selectedItem;
	}

	public function hasSelectedItem(): bool {
		return $this->_selectedItem instanceof Item;
	}

	public function setSelectedItem(Item $menuItem) {		
		$this->_selectedItem = $menuItem;
	}
	
	public function unselectItem() {
		$this->_selectedItem = null;
	}
	
	public function createItem($text, $shortCut = '', array $params = array()): Item {
		return $this->createObject(Item::class, array_merge($params, array(
			'text' => $text,
			'shortCut' => $shortCut,
		)));
	}
	
	public function createSeparator() {
		return $this->createObject(Separator::class);
	}
	
	public function render(): string {
		$content = $this->getChildObjects()->render();
		$html = new StringChar('<table cellspacing="0" id="__id__" class="MenuList"__style__>__content__</table>');
		$html->replace('__id__',      $this->getObjectID());
		$html->replace('__content__', $content);
		$html->replace('__style__', $this->getInlineStyle(true));
		
		return $html;
	}
}