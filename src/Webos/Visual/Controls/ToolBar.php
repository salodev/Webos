<?php
namespace Webos\Visual\Controls;
class ToolBar extends \Webos\Visual\Control {
	
	use \Webos\Visual\ControlsFactory;

	public function toolItems() {
		return $this->_childObjects;//->getObjectsByClassName('ToolItem');
	}

	public function getAllowedActions(){
		return array();
	}

	public function getAvailableEvents(){
		return array();
	}

	/**
	 * 
	 * @param string $title
	 * @param array $options
	 * @return Button
	 */
	public function addButton($title, array $options = array()) {
		return $this->createObject(Button::class, array_merge(array(
			'value'=>$title,
			'left'=>'5px'), 
		$options));
	}
	
	public function addSeparator() {
		$this->createObject(VerticalSeparator::class);
	}
	
	public function render() {
		$html = '<div class="Toolbar"'.$this->getInlineStyle(true).'>';
		$html .= $this->toolItems()->render();
		$html .= '</div>';
		return $html;
	}

}