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

	public function addButton($title, array $options = array()) {
		return $this->createObject('\Webos\Visual\Controls\ToolItem', array_merge(array(
			'title'=>$title,
			'left'=>'5px'), 
		$options));
	}
	
	public function addSeparator() {
		$this->createObject('\Webos\Visual\Controls\ToolBarSeparator');
	}
	
	public function render() {
		$html = '<div class="Toolbar">';
		$html .= $this->toolItems()->render();
		$html .= '</div>';
		return $html;
	}

}