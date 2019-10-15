<?php

namespace Webos\Visual\Controls;

use Webos\Visual\HtmlTag;

/**
 * @property checked;
 * @property checkedValue;
 * @property uncheckedValue;
 */
class CheckBox extends Field {
	
	public function getInitialAttributes(): array {
		return [
			'checkedValue'   => true,
			'uncheckedValue' => true,
		];
	}
	
	public function initialize(array $params = []) {
		parent::initialize($params);
		$this->width = null;
	}
	
	public function render(): string {
		$tag = new HtmlTag('input', [
			'type'  => 'checkbox',
			'id'    => $this->getObjectID(),
			'webos' => null,
			'click' => null,
			'style' => $this->getInlineStyle(true, false),
		]);
		
		if ($this->checked) {
			$tag->setAttribute('checked', 'checked');
		}
		
		return $tag->render();
	}
	
	/**
	 * Exposed method click
	 */
	public function action_click(): void {
		if ($this->checked) {
			$this->value   = $this->uncheckedValue;
			$this->checked = false;
		} else {
			$this->value   = $this->checkedValue;
			$this->checked = true;
		}
		parent::action_click();
	}
	
	/**
	 * Setter method for value property
	 * @throws \Exception
	 */
	public function __set_value($value) {
		if ($value === $this->checkedValue) {
			$this->checked = true;
			return;
		}
		if ($value === $this->uncheckedValue) {
			$this->checked = false;
			return;
		}
		throw new \Exception('Incorrect value assingment');
	}
	
	/**
	 * Getter method for value property
	 */
	public function __get_value() {
		return $this->checked ? $this->checkedValue : $this->uncheckedValue;
	}
}