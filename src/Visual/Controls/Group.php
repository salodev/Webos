<?php
namespace Webos\Visual\Controls;

use Exception;

class Group extends Field {
	
	public function initialize(array $params = []) {
		
		if (empty($params['className'])) {
			throw new Exception('Missing className parameter for initialize Group Control');
		}
		$labelWidth = $params['labelWidth'] ?? 100;
		$this->label = $this->createObject(Label::class, [
			'top'    => 0, 
			'bottom' => 0, 
			'left'   => 0,
			'text'   => $params['label'] ?? 'Label text',
			'width'  => $labelWidth,
		]);
		
		$this->control = $this->createObject($params['className'], array_merge($params, [
			'top'    => 0, 
			'bottom' => 0,
			'width'  => $params['width'] - $labelWidth,
			'left'   => $labelWidth,
		]));
	}
	
	public function createButton(string $text, array $params = []): Button {
		$width = $params['width'] ?? 30; $margin = 10;
		
		$this->control->width = $this->control->width -($width + $margin);
		return $this->control->button = $this->button = $this->createObject(Button::class, array_merge($params, [
			'top'    => 0, 
			'bottom' => 0, 
			'left'   => $this->width - $width,
			'width'  => $width,
			'value'  => $text,
		]));
	}
	
	public function getLabel(): Label {
		return $this->label;
	}
	
	public function getControl(): Field {
		return $this->control;
	}
	
	public function getButton(): Button {
		return $this->button;
	}
	
	public function render(): string {
		$htmlChilds = $this->getChildObjects()->render();
		$inlineStyle = $this->getInlineStyle();
		$html  = '';
		$html .= "<div class=\"Group\" {$inlineStyle}>";
		$html .= $htmlChilds;
		$html .= '</div>';
		return $html;
	}
	
	public function __set_disabled($value) {
		$this->control->disabled = $value;
		if ($this->button) {
			$this->button->disabled = $value;
		}
	}
	
	public function __get_value() {
		return $this->control->value;
	}
	
	public function __set_value($value) {
		return $this->control->value = $value;
	}
}
