<?php
namespace Webos\Visual\Windows;

use Webos\Visual\Window;
use Webos\Visual\Controls\Menu\ListItems;

class Application extends Window {
	
	public function getInitialAttributes(): array {
		return [
			'top'             => 0,
			'bottom'          => 0,
			'right'           => 0,
			'left'            => 0,
			'backgroundColor' => '#f9f9f9',
			'width'           => null,
			'height'          => null,
		];
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
