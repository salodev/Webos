<?php
namespace Webos\Visual;
class ApplicationWindow extends Window {
	
	public function render() {
		$html  = '<div style="position:absolute;display:block;top:0;left:0;right:0;">';
		$html .= $this->getChildObjects()->render();
		$html .= '</div>';
		return $html;
	}
}
