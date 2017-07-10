<?php
namespace Webos\Visual\Controls\Menu;
use Webos\Exceptions\Collection\NotFound;
class Button extends \Webos\Visual\Control {

	public function initialize() {
		$ws = $this->getParentApp()->getWorkSpace();
		$ws->addEventListener('actionCalled', function ($source, $eventName, $params) {
			if ($params['object'] === $this) {
				return true;
			}
			try {
				$parentObject = $params['object']->getParentByClassName(self::class);
			} catch (\TypeError $e) {
				$this->close();
				return $this;
			}
			//return;
			if ($parentObject !== $this) {
				$this->close();
			}
			return true;
		});
	}

	public function  getAllowedActions(): array {
		return array(
			'press',
		);
	}

	public function  getAvailableEvents(): array {
		return array(
			'press'
		);
	}

	public function press() {
		// $this->selected = true;
		if ($this->triggerEvent('press')) {
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

		$onclick = "__doAction('send',{actionName:'press',objectId:'".$this->getObjectID()."'});";

		$html = new \Webos\StringChar(
			'<div id="__id__" class="MenuButton__selected__">' .
				'<div class="text" onclick="__onclick__">__text__</div>' .
				'<div class="container">__content__</div>' .
			'</div>'
		);
		
		$html->replaces(array(
			'__id__'       => $this->getObjectID(),
			'__selected__' => $selected,
			'__text__'     => $this->text,
			'__content__'  => $content,
			'__onclick__'  => $onclick,
		));

		return $html;
	}

}