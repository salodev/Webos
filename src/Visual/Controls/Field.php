<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;

abstract class Field extends Control {
	protected $_linkedField = null;
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
		$this->button = $this->getParent()->createObject(Button::class, array(
			'top' => $top,
			'left' => $left,
			'width' => $width,
			'value' => $text,
		));
		return $this->button;
	}
	
	public function attachLabel(string $name, int $width = 200, int $left = 10): Button {
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
	
	public function captureTyping(bool $value = null): bool {
		if ($value !== null) {
			$this->_captureTyping = $value;
		}
		return $this->_captureTyping;
	}
	
	public function enable(bool $value = true): self {
		$this->disabled = !$value;
		return $this;
	}
	
	public function disable(bool $value = true): self {
		$this->disabled = $value;
		return $this;
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
