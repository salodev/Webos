<?php
namespace Webos\Visual\Controls\Menu;
class Button extends \Webos\Visual\Control {

	public function initialize() {
		$ws = $this->getParentApp()->getWorkSpace();
		$ws->addEventListener('actionCalled', array($this, 'onActionCalled'));
	}

	public function  getAllowedActions() {
		return array(
			'press',
		);
	}

	public function  getAvailableEvents() {
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
		foreach($this->getObjectsByClassName('\Webos\Visual\Controls\Menu\Item') as $menuItem) {
			if ($menuItem->selected) {
				$menuItem->selected = false;
			}
		}
	}

	// Para saber si el botón está seleccionado, hay que preguntarle
	// al objeto contenedor que guarda esta información.
	public function __get_selected() {		
		$selected = $this->getParent()->getSelectedButton();
		if ($selected instanceof \Webos\Visual\Controls\Menu\Button) {
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
	
	public function onActionCalled($source, $eventName, $params) {
		//return null;
		// Si no está seleccionado, no hay nada que hacer.
		if (!$this->selected) { return null; }
			
		// Si no se proporciona objectId terminamos.
		if (empty($params['objectId'])) { return null; }

		// Si el objeto sobre el que se realiza la acción es el MenuButton
		// activo terminamos.
		if ($params['objectId'] == $this->getObjectID()) {
			return null;
		}

		// Si el objeto en cuestión, es hijo del MenuButton activo, también
		// terminamos.
		if ($this->getObjectByID($params['objectId'])) {				
			return null;
		}

		// Caso contrario, se ha hecho clik en un elemento fuera del menú,
		// entonces hay que cerrarlo.
		$this->close();
	}
	
	public function render() {
		$content = '';
		
		$selected = '';
		$selectedButton = $this->getParent()->getSelectedButton();
		if ($selectedButton instanceof \Webos\Visual\Controls\Menu\Button) {
			if ($selectedButton->getObjectID()== $this->getObjectID()) {
				$selected = ' selected';
			}
		}

		if ($selected) {
			$content .= $this->getChildObjects()->render();

			foreach($this->getObjectsByClassName('\Webos\Visual\Controls\Menu\Item') as $menuItem) {
				if ($menuItem->selected) {
					$content .= $menuItem->getChildObjects()->render();
				}
			}
		}

		$onclick = "__doAction('send',{actionName:'press',objectId:'".$this->getObjectID()."'});";

		$html = new \Webos\String(
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