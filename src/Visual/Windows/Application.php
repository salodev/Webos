<?php
namespace Webos\Visual\Windows;

use Webos\Visual\Window;
use Webos\Visual\Controls\Menu\ListItems;

class Application extends Window {
	
	public function preInitialize(): void {
		$this->top    = 0;
		$this->bottom = 0;
		$this->right  = 0;
		$this->left   = 0;
		$this->backgroundColor = '#f9f9f9';
	}
	
	public function render(): string {
		$stylesString = $this->getInlineStyle(true);
		$html  = '<div id="'.$this->getObjectID(). '" ' . $stylesString .' webos container-key-receiver class="Window">';
		$html .= '<div class="container" ' . $stylesString . '>';
		$html .= $this->getChildObjects()->render();
		
		$html .= '</div>';
		$html .= '</div>';
		return $html;
	}
	
	public function createMenu($text): ListItems {
		if (!$this->menuBar) {
			$this->menuBar = $this->createMenuBar();
		}
		return $this->menuBar->createButton($text);
	}
}
