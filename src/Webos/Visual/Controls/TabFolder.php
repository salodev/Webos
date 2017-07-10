<?php

namespace Webos\Visual\Controls;
class TabFolder extends \Webos\Visual\Control {

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
		$html = '<div id="' . $this->getObjectID() . '" class="TabFolder">';
		$html .= $this->getChildObjects()->render();
		$html .= '</div>';

		return $html;
	}
}