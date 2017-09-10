<?php
namespace Webos\Visual\Controls\Menu;

use Webos\Visual\Control;
use Webos\StringChar;
use TypeError;

class Item extends Control {

	public function  getAllowedActions(): array {
		return array(
			'click'
		);
	}

	public function  getAvailableEvents(): array {
		return array('click');
	}

	public function click() {
		
		if ($this->triggerEvent('click')) {
			if ($this->getObjectsByClassName(ListItems::class)->count()) {
				$this->selected = true;
				$this->getParentByClassName(Button::class)->modified();
			} else {
				try {
					$this->getParentByClassName(Button::class)->close();
				} catch (TypeError $e) {
					
				}
			}
		}
	}
	
	function createItem(string $text, string $shortCut = '', array $params = []): Item {
		$listItems = $this->_getListItems();
		return $listItems->createItem($text, $shortCut, $params);
	}
	
	public function createSeparator(): Separator {
		$listItems = $this->_getListItems();
		return $listItems->createObject(Separator::class);
	}
	
	private function _getListItems():ListItems {
		$ret = $this->getObjectsByClassName(ListItems::class);
		if ($ret->count() != 1) {
			$this->createObject(ListItems::class);
			$ret = $this->getObjectsByClassName(ListItems::class);
		}
		$listItems = $ret->item(0);
		return $listItems;
	}

	public function __get_selected() {		
		if (!$this->getParent()->hasSelectedItem()) {
			return false;
		}
		return $this->getParent()->getSelectedItem() === $this;
	}

	public function __set_selected($value) {
		$currentValue = $this->selected;
		if ($value === $currentValue) {
			return;
		}
		if ($value) {
			$this->getParent()->setSelectedItem($this);
		} else {
			$this->getParent()->unselectItem();
		};
	}
	
	public function render(): string {
		$content  = '';
		$selected = '';

		if ($this->selected) {
			$selected = ' selected';
		}

		$arr = ($this->getChildObjects()->count()) ? '►' : '';
		if (!$arr) {
			$arr = ($this->shortCut) ? $this->shortCut : '';
		}

		$html = new StringChar(
			'<tr id="__id__" class="MenuItem__selected__"__disabled__ webos click>' .
				'<td class="icon__icon_class__"></td>' .
				'<td class="text">__text__</td>' .
				'<td class="arrow__arrow__">__arr__</td>' .
			'</tr>'
		);
		$html->replaces(array(
			'__id__'         => $this->getObjectID(),
			'__selected__'   => $selected,
			'__disabled__'   => $this->disabled ? 'disabled="disabled"' : '',
			'__icon_class__' => '',
			'__text__'       => $this->text,
			'__arrow__'      => '',
			'__arr__'        => $arr,
			'__content__'    => $content,
		));
		
		return $html;
	}

}