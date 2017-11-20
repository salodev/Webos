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

	public function select(): self {
		if ($this->triggerEvent('select')) {
			$this->getParent()->setActiveTab($this);
		}
		return $this;
	}
	
	public function isActive(): bool {
		if (!$this->getParent()->hasActiveTab()) {
			return false;
		}
		return $this->getParent()->getActiveTab()===$this;
	}

	public function  getAllowedActions(): array {
		return array(
			'select',
		);
	}

	public function  getAvailableEvents(): array {
		return array(
			'embedded',
			'select',
		);
	}
	
	public function onEmbedded(callable $function): self {
		$this->bind('embedded', $function);
		return $this;
	}
	
	public function onSelect(callable $function): self {
		$this->bind('select', $function);
		return $this;
	}
	
	public function render(): string {
		//$html = '<div id="' . $this->getObjectID() . '" class="TabFolder">';
		$html = $this->getChildObjects()->render();
		// $html .= '</div>';

		return $html;
	}
	
	public function embedWindowOnSelect(string $windowClassName, array $initialAttributes = array(), callable $onEmbed = null) {
		if ($this->getParent()->hasActiveTab()) {
			if ($this->getParent()->getActiveTab() === $this) {
				$window = $this->embedWindow($windowClassName, $initialAttributes);
				$this->triggerEvent('embedded', [
					'window' => $window,
					'object' => $window,
				]);
				$this->_initialized = true;
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
			$window = $this->embedWindow($this->_windowToEmbedClassName, $this->_windowToEmbedInitialAttributes);
			$this->triggerEvent('embedded', [
				'window' => $window,
				'object' => $window,
			]);			
		});
		return $this;
	}
}