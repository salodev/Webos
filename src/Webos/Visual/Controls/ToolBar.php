<?php
namespace Webos\Visual\Controls;
use \Webos\ObjectsCollection;
class ToolBar extends \Webos\Visual\Control {
	
	use \Webos\Visual\ControlsFactory;

	public function toolItems(): ObjectsCollection {
		return $this->_childObjects;//->getObjectsByClassName('ToolItem');
	}

	public function getAllowedActions(): array {
		return array();
	}

	public function getAvailableEvents(): array {
		return array();
	}

	/**
	 * 
	 * @param string $title
	 * @param array $options
	 * @return Button
	 */
	public function addButton($title, array $options = array()): Button {
		return $this->createObject(Button::class, array_merge(array(
			'value' => $title,
			// 'left'  => 5,
		),  $options));
	}
	
	public function addSeparator(): VerticalSeparator {
		$this->createObject(VerticalSeparator::class);
	}
	
	public function render(): string {
		$html = '<div class="Toolbar"'.$this->getInlineStyle(true).'>';
		$html .= $this->toolItems()->render();
		$html .= '</div>';
		return $html;
	}

}