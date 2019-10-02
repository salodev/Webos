<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use salodev\Validator;

abstract class Field extends Control {
	protected $_linkedField   = null;
	protected $_captureTyping = false;

	public function setLinkedField(&$field){
		$this->_linkedField = $field;
	}

	public function __set_value($value) {
		if ($this->_linkedField) {
			$this->_linkedField->value = $value;
		}
	}

	/*public function __get_value($name) {
		if ($this->_linkedField) {
			$value = &$this->_attributes[$name];
			if ($value !== $this->_linkedField->value) {
				$this->__set($name, $this->_linkedField->value);
			}
		}
	}*/
	
	public function setValue($value): self {
		$this->value = $value;
		$this->triggerEvent('updateValue', [
			'value' => $this->value,
		]);
		return $this;
	}

	public function action_setValue(array $params = []): void {
		if (!isset($params['value'])) {
			return;
		}
		$this->setValue($params['value']);
	}
	
	public function onUpdateValue(callable $callback): Field {
		$this->bind('updateValue', $callback);
		return $this;
	}
	
	public function captureTyping(bool $value = null): bool {
		if ($value !== null) {
			$this->_captureTyping = $value;
		}
		return $this->_captureTyping;
	}
	
	/**
	 * Alias for onUpdateValue. Easy to remember by using of jQuery or
	 * HTML DOM events.
	 * 
	 * @param \Webos\Visual\Controls\callable $callback
	 * @return \Webos\Visual\Controls\Field
	 */
	public function onChange(callable $callback): Field {
		$this->bind('updateValue', $callback);
		return $this;
	}
}
