<?php
namespace Webos\Visual\Controls;
abstract class Field extends \Webos\Visual\Control {
	protected $_linkedField = null;

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

	public function setValue($mixed) {
		if (is_array($mixed)) {
			if (!isset($mixed['value'])) {
				return false;
			}
			$this->value = $mixed['value'];
		} else {
			$this->value = $mixed;
		}
		$this->triggerEvent('updateValue', array(
			'value' => $this->value,
		));

	}
	
	/**
	 * Creates an visual attached button to field.
	 * @param type $text
	 * @param type $width
	 * @return \Webos\Visual\Controls\Button
	 */
	public function attachButton($text, $width = 50) {
		$top = $this->top;
		$left = $this->left + $this->width - $width;
		//die("{$this->left} + {$this->width} - {$width} = {$left}");
		$this->width = ($this->width - $width - 5) . 'px';
		return $this->getParentWindow()->createObject('\Webos\Visual\Controls\Button', array(
			'top' => $top,
			'left' => $left . 'px',
			'width' => $width . 'px',
			'value' => $text,
		));
	}
	
	public function onUpdateValue(callable $callback) {
		$this->bind('updateValue', $callback);
		return $this;
	}

	public function getAllowedActions() {
		return array(
			'setValue'
		);
	}

	public function getAvailableEvents() {
		return array(
			'updateValue',
		);
	}
}
