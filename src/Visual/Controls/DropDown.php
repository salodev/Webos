<?php
namespace Webos\Visual\Controls;
use Webos\Visual\Control;
use Webos\Visual\Controls\Menu\ListItems;
use Webos\Visual\Controls\Menu\Item;
use Webos\StringChar;

class DropDown extends Control {
	
	public function initialize(array $params = []) {
		$this->listItems = $this->createObject(ListItems::class, [
			'top' => 100,
			'left' => 100,
			'position' => 'fixed',
		]);
	}

	public function action_click(array $params = []): void {
		$this->triggerEvent('click');
		$menu = $this->getParentWindow()->createContextMenu($params['top']+25, $params['left']);
		$this->triggerEvent('contextMenu', ['menu'=>$menu]);
	}
	
	public function onContextMenu(callable $cb, bool $persistent = true, array $contextData = []): self {
		$this->bind('contextMenu', $cb, $persistent, $contextData);
		return $this;
	}
	
	public function createItem($text): Item {
		return $this->listItems->createItem($text);
	}

	
	public function render(): string {
		$html = new StringChar(
			'<button id="__id__" class="Button DropDown" type="button" name="__name__" webos set-object-pos action="click" __style____disabled__>__value__<span class="icon"></span></button>'
		);
		
		$html->replace('__style__', $this->getInLineStyle());
			
		$html->replaces([
			'__id__'       => $this->getObjectID(),
			'__name__'     => $this->name,
			'__value__'    => $this->value,
			'__content__'  => $this->getChildObjects()->render(), 
			'__disabled__' => $this->disabled ? 'disabled="disabled"' : '',
		]);

		return $html;
	}
}