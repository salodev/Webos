<?php
namespace Webos\Visual\Controls\Menu;
class Item extends \Webos\Visual\Control {

	public function  getAllowedActions() {
		return array(
			'press'
		);
	}

	public function  getAvailableEvents() {
		return array('press');
	}

	public function press() {
		
		if ($this->triggerEvent('press')) {
			if ($this->getObjectsByClassName(__NAMESPACE__ . '\ListItems')->count()) {
				$this->selected = true;
			} else {
				$parent = $this->getParentByClassName(__NAMESPACE__ . '\Button');
				if ($parent instanceof Button) {
					$parent->close();
				}
			}

			$this->getParentByClassName(__NAMESPACE__ . '\Button')->modified();
			
		}
	}
	
	function createItem($text, $shortCut = '', array $params = array()) {
		$listItems = $this->_getListItems();
		return $listItems->createItem($text, $shortCut, $params);
	}
	
	public function createSeparator() {
		$listItems = $this->_getListItems();
		return $listItems->createObject(Separator::class);
	}
	
	private function _getListItems() {
		$ret = $this->getObjectsByClassName(ListItems::class);
		if ($ret->count() != 1) {
			$this->createObject(ListItems::class);
			$ret = $this->getObjectsByClassName(ListItems::class);
		}
		$listItems = $ret->item(0);
		return $listItems;
	}

	public function __get_selected() {		
		$selected = $this->getParent()->getSelectedItem();
		if ($selected instanceof Item) {
			if ($selected->getObjectID() == $this->getObjectID()) {				
				return true;
			}
		}		
		return false;
	}

	public function __set_selected($value) {
		if ($value) {
			$this->getParent()->setSelectedItem($this);
		} else {
			if ($this->selected) {
				$this->getParent()->setSelectedItem(null);
			}
		}
	}
	
	public function render() {
		$content  = '';
		$selected = '';

		if ($this->selected) {
			$selected = ' selected';
		}

		$onclick = "__doAction('send',{actionName:'press',objectId:this.id});";
		$arr = ($this->getChildObjects()->count()) ? '►' : '';
		if (!$arr) {
			$arr = ($this->shortCut) ? $this->shortCut : '';
		}

		$html = new \Webos\String(
			'<tr id="__id__" class="MenuItem__selected__" onclick="__onclick__">' .
				'<td class="icon__icon_class__"></td>' .
				'<td class="text">__text__</td>' .
				'<td class="arrow__arrow__">__arr__</td>' .
			'</tr>'
		);
		$html->replaces(array(
			'__id__'         => $this->getObjectID(),
			'__selected__'   => $selected,
			'__icon_class__' => '',
			'__text__'       => $this->text,
			'__onclick__'    => $onclick,
			'__arrow__'      => '',
			'__arr__'        => $arr,
			'__content__'    => $content,
		));
		
		return $html;
	}

}