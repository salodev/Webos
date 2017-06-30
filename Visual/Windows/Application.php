<?php
namespace Webos\Visual\Windows;
use \Webos\Visual\Window;
class Application extends Window {
	
	public function render() {
		$html  = '<div id="'.$this->getObjectID().'" style="position:absolute;display:block;top:0;left:0;right:0;">';
		$html .= $this->getChildObjects()->render();
		$html .= '</div>';
		return $html;
	}
}
