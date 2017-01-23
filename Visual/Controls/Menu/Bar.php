<?php
namespace Webos\Visual\Controls\Menu;
/**
 * Barra de menús. Contenedora de MenuButtons.
 */
class Bar extends \Webos\Visual\Control {

	protected $_selectedButton = null;

	public function __get_text() {
		if (empty($this->_data['text'])) {
			$count = $this->getParent()->getObjectsByClassName('\Webos\Visual\Controls\Menu\Bar')->count();
			return 'MenuBar' . $count;
		}
	}
	
	public function getSelectedButton() {
		return $this->_selectedButton;
	}

	// No se hace tipado del tipo de valor que acepta el método.
	public function setSelectedButton($menuButton) {
		$this->_selectedButton = $menuButton;
	}
	
	public function createButton($text) {
		$menuButton = $this->createObject('\Webos\Visual\Controls\Menu\Button', array(
			'text' => $text,
		));
		return $menuButton->createObject('\Webos\Visual\Controls\Menu\ListItems');
	}
	
	public function render() {
		$html  = '<div class="MenuBar">';
		$html .= $this->getChildObjects()->render();
		$html .= '</div>';
		return $html;
	}
}