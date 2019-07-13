<?php
namespace Webos\Visual\Controls\Menu;

use Webos\Visual\Control;

/**
 * Barra de menús. Contenedora de MenuButtons.
 */
class Bar extends Control {

	protected $_selectedButton = null;

	public function __get_text() {
		if (empty($this->_data['text'])) {
			$count = $this->getParent()->getObjectsByClassName(Bar::class)->count();
			return 'MenuBar' . $count;
		}
	}
	
	public function initialize(array $params = []) {
		$this->height = 26;
		$this->top    = 0;
		$this->left   = 0;
		$this->right  = 0;
	}
	
	public function getSelectedButton() {
		return $this->_selectedButton;
	}

	// No se hace tipado del tipo de valor que acepta el método.
	public function setSelectedButton($menuButton) {
		$this->_selectedButton = $menuButton;
	}
	
	public function createButton(string $text): ListItems {
		$menuButton = $this->createObject(Button::class, [
			'text' => $text,
		]);
		return $menuButton->createObject(ListItems::class);
	}
	
	public function render(): string {
		$html  = '<div id="'.$this->getObjectID().'" '.$this->getInlineStyle(true).' class="MenuBar container">';
		$html .= $this->getChildObjects()->render();
		$html .= '</div>';
		return $html;
	}
}