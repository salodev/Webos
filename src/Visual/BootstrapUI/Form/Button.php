<?php
namespace Webos\Visual\BootstrapUI\Form;
use Webos\Visual\Controls\Button as Base;

class Button extends Base {
	
	public function render(): string {
		return "<button 
			id=\"{$this->getObjectID()}\" 
			type=\"button\" 
			class=\"btn 
			btn-{$this->color}\"
			webos click
			>{$this->text}</button>";
	}
	
	public function setColor($value) {
		$this->color = $value;
	}
	
	public function setBlue() {
		$this->setColor('primary');
	}
	
	public function setGray() {
		$this->setColor('secondary');
	}
	
	public function setGeen() {
		$this->setColor('success');
	}
	
	public function setRed() {
		$this->setColor('danger');
	}
	
	public function setYellow() {
		$this->setColor('warning');
	}
	
	public function setLightBlue() {
		$this->setColor('info');
	}
	
	public function setWhite() {
		$this->setColor('light');
	}
	
	public function setDarkGray() {
		$this->setColor('dark');
	}
}