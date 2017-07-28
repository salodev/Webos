<?php

namespace Webos\Visual;
use Webos\Visual\Controls\TextBox;
use Webos\Visual\Controls\ComboBox;
use Webos\Visual\Controls\Button;

/**
 * @todo Add mehtods for all controls.
 */
trait ControlsFactory {
	
	/**
	 * 
	 * @param array $params
	 * @return Controls\TextBox
	 */
	public function createTextBox(array $params = []): TextBox {
		return $this->createObject(TextBox::class, $params);
	}
	
	/**
	 * 
	 * @param array $params
	 * @return Controls\Button
	 */
	public function createButton(string $text = 'Button', array $params = []): Button {
		$params['value'] = $params['value'] ?? $text;
		return $this->createObject(Button::class, $params);
	}
	
	public function createComboBox(array $params = []): ComboBox {
		return $this->createObject(ComboBox::class, $params);
	}
}

