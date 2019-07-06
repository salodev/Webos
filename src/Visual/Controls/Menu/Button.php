<?php
namespace Webos\Visual\Controls\Menu;

use Webos\Visual\Control;
use Webos\StringChar;
use TypeError;

class Button extends Control {

	public function initialize() {
		$this->getApplication()->getWorkSpace()->addEventListener('actionCalled', function ($params) {
			$selected = $this->selected;
			if (!$selected || $params['object'] === $this) {
				return;
			}
			try {
				$parentObject = $params['object']->getParentByClassName(Button::class);
			} catch (TypeError $e) {
				$this->close();
				return true;
			}
			
			if ($parentObject !== $this) {
				$this->close();
			}
		});
	}

	public function  getAllowedActions(): array {
		return [
			'click',
		];
	}

	public function  getAvailableEvents(): array {
		return [
			'click'
		];
	}

	public function click() {
		// $this->selected = true;
		if ($this->triggerEvent('click')) {
			$a = $this->selected;
			if (!$this->selected) {
				$this->selected = true;
			} else {
				$this->selected = false;
			}
		}
	}
	
	public function close() {
		$selected = $this->selected = false;
		// Al cerrarse el botón de menú, debe des-seleccionar todos los
		// items de menú que hayan quedado seleccionados.
		foreach($this->getObjectsByClassName(Item::class) as $menuItem) {
			if ($menuItem->selected) {
				$menuItem->selected = false;
			}
		}
	}

	// Para saber si el botón está seleccionado, hay que preguntarle
	// al objeto contenedor que guarda esta información.
	public function __get_selected() {		
		$selected = $this->getParent()->getSelectedButton();
		if ($selected instanceof self) {
			if ($selected === $this) {
				return true;
			}
		}

		return false;
	}

	// Para establecer si está seleccionado, se debe solicitar al objeto
	// contenedor que guarde esta información.
	public function __set_selected($value) {
		$a = $this->selected;
		
		if ($value){
			$this->getParent()->setSelectedButton($this);
		} else {
			if ($this->selected) {
				$this->getParent()->setSelectedButton(null);
			}
		}
	}
	
	public function render(): string {
		$content = '';
		
		$selected = '';
		$selectedButton = $this->getParent()->getSelectedButton();
		if ($selectedButton instanceof self) {
			if ($selectedButton->getObjectID()== $this->getObjectID()) {
				$selected = ' selected';
			}
		}

		if ($selected) {
			$content .= $this->getChildObjects()->render();

			foreach($this->getObjectsByClassName(Item::class) as $menuItem) {
				if ($menuItem->selected) {
					$content .= $menuItem->getChildObjects()->render();
				}
			}
		}

		$html = new StringChar(
			'<div id="__id__" class="MenuButton__selected__">' .
				'<div class="text" webos click>__text__</div>' .
				'<div class="container">__content__</div>' .
			'</div>'
		);
		
		$html->replaces([
			'__id__'       => $this->getObjectID(),
			'__selected__' => $selected,
			'__text__'     => $this->text,
			'__content__'  => $content,
		]);

		return $html;
	}

}