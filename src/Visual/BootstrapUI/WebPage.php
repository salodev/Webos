<?php

namespace Webos\Visual\BootstrapUI;
use Webos\Visual\Window;
use Webos\Visual\BootstrapUI\Form\Button;

class WebPage extends Window {
	
	public function addButton(): Button {
		return $this->createObject(Button::class);
	}
			
	public function render(): string {
		return '<div>' . $this->getChildObjects()->render() . '</div>';
	}
	
}
