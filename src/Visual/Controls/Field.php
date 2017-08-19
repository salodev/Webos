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
	public function attachButton(string $text = '...', int $width = 25): Button {
		$top = $this->top;
		$left = $this->left + $this->width - $width;
		//die("{$this->left} + {$this->width} - {$width} = {$left}");
		$this->width = $this->width - $width - 5;
		$this->button = $this->getParentWindow()->createObject(Button::class, array(
			'top' => $top,
			'left' => $left,
			'width' => $width,
			'value' => $text,
		));
		return $this->button;
	}
	
	public function attachLabel(string $name, int $width = 200, int $left = 10): Label {
		$top = $this->top;
		$left += $this->left;
		if ($this->button instanceof Button) {
			$left += $this->button->width;
		}
		//die("{$this->left} + {$this->width} - {$width} = {$left}");
		$this->width = $this->width - $width - 5;
		$this->label = $this->getParentWindow()->createObject(Label::class, array(
			'top'   => $top,
			'left'  => $left,
			'width' => $width,
			'name'  => $name,
		));
		return $this->button;
	}
	
	public function onUpdateValue(callable $callback): Field {
		$this->bind('updateValue', $callback);
		return $this;
	}

	public function getAllowedActions(): array {
		return array(
			'setValue'
		);
	}

	public function getAvailableEvents(): array {
		return array(
			'updateValue',
		);
	}
}
