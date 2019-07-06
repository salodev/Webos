<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\Visual\ControlsFactory;

class ToolBar extends Control {
	
	use ControlsFactory;

	public function getAllowedActions(): array {
		return [];
	}

	public function getAvailableEvents(): array {
		return [];
	}

	/**
	 * 
	 * @param string $title
	 * @param array $options
	 * @return Button
	 */
	public function addButton($title, array $options = []): Button {
		return $this->createObject(Button::class, array_merge([
			'value' => $title,
			// 'left'  => 5,
		],  $options));
	}
	
	public function addSeparator(): VerticalSeparator {
		$this->createObject(VerticalSeparator::class);
	}
	
	public function render(): string {
		$fixed = $this->fixedTo ?? 'top';
		$horizontalAlign = ($this->horizontalAlign ?: '')=='right'?'contentRight':'';
		$inlineStyle = ''; //$this->getInlineStyle();
		$html = "<div class=\"Toolbar {$fixed} {$horizontalAlign}\" {$inlineStyle}>";
		$html .= $this->getChildObjects()->render();
		$html .= '</div>';
		return $html;
	}

}