<?php
namespace Webos\Visual\Windows;

use Webos\Visual\Window;

class Application extends Window {
	
	public function preInitialize() {
		$this->top    = 0;
		$this->bottom = 0;
		$this->right  = 0;
		$this->left   = 0;
	}
	
	public function render(): string {
		$stylesString = $this->getInlineStyle(true);
		$html  = '<div id="'.$this->getObjectID(). '" ' . $stylesString .'>';
		$html .= '<div class="container" ' . $stylesString . '>';
		$html .= $this->getChildObjects()->render();
		
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}
}
