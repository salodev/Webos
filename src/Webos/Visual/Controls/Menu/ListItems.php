<?php
namespace Webos\Visual\Controls\Menu;
class ListItems extends \Webos\Visual\Control {

	protected $_selectedItem = null;

	public function  getAvailableEvents() {
		return array();
	}

	public function  getAllowedActions() {
		return array();
	}

	public function getSelectedItem() {
		return $this->_selectedItem;
	}

	public function setSelectedItem($menuItem) {		
		$this->_selectedItem = $menuItem;
	}
	
	public function createItem($text, $shortCut = '', array $params = array()) {
		return $this->createObject(Item::class, array_merge($params, array(
			'text' => $text,
			'shortCut' => $shortCut,
		)));
	}
	
	public function createSeparator() {
		return $this->createObject(Separator::class);
	}
	
	public function render() {
		$content = $this->getChildObjects()->render();
		$html = new \Webos\String('<table cellspacing="0" id="__id__" class="MenuList">__content__</table>');
		$html->replace('__id__',      $this->getObjectID());
		$html->replace('__content__', $content);
		return $html;
	}
}