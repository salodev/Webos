<?php
namespace Webos\Visual\Windows;
use \Webos\Visual\Window;
class Application extends Window {
	
	public function render(): string {
		$html  = '<div id="'.$this->getObjectID().'">';
		$html .= '<div class="container" style="position:absolute;display:block;top:0;left:0;right:0;bottom:0;">';
		$html .= $this->getChildObjects()->render();
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}
}
