<?php

namespace Webos\Visual\Controls;
use Webos\Visual\Control;
use Webos\Visual\FormContainer;


class TabFolder extends Control {
	
	use FormContainer;

	public function initialize() {
		if (!$this->title) {
			$this->title = 'Pestaña ' . $this->getParent()->getChildObjects()->count();
		}
	}

	public function select() {
		if ($this->triggerEvent('select')) {
			$this->getParent()->setActiveTab($this);
		}
	}

	public function  getAllowedActions(): array {
		return array(
			'select',
		);
	}

	public function  getAvailableEvents(): array {
		return array(
			'select',
		);
	}
	
	public function render(): string {
		//$html = '<div id="' . $this->getObjectID() . '" class="TabFolder">';
		$html = $this->getChildObjects()->render();
		// $html .= '</div>';

		return $html;
	}
	
	public function embedWindowOnSelect(string $windowClassName, array $initialAttributes = array()) {
		if ($this->getParent()->hasActiveTab()) {
			if ($this->getParent()->getActiveTab() === $this) {
				return $this->embedWindow($windowClassName, $initialAttributes);
			}
		}
		$this->_windowToEmbedClassName = $windowClassName;
		$this->_windowToEmbedInitialAttributes = $initialAttributes;
		$this->bind('select', function() {
			// In order to prevente multi-embed same window, I need
			// mark it that was initialized.
			
			if ($this->_initialized) {
				return;
			}
			$this->_initialized = true;			
			return $this->embedWindow($this->_windowToEmbedClassName, $this->_windowToEmbedInitialAttributes);
		});
		return true;
	}
}