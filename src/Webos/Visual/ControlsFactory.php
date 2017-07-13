<?php

namespace Webos\Visual;
/**
 * @todo Add mehtods for all controls.
 */
trait ControlsFactory {
	
	/**
	 * 
	 * @param array $params
	 * @return Controls\TextBox
	 */
	public function createTextBox(array $params = []) {
		return $this->createObject(Controls\TextBox::class, $params);
	}
	
	/**
	 * 
	 * @param array $params
	 * @return Controls\Button
	 */
	public function createButton(array $params = []) {
		return $this->createObject(Controls\Button::class, $params);
	}
}

