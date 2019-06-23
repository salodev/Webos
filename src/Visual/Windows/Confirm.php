<?php
namespace Webos\Visual\Windows;

use Webos\Visual\Window;

class Confirm extends Window {

	public function initialize(array $params = []) {
		$this->title  = 'Confirmar';
		$this->height = 130;
		$this->text   = $this->message;
		$this->modal  = true;
		
		$this->createWindowButton('Confirmar')->onClick(function() {
			$this->triggerEvent('confirm');
			$this->close();
		});
		$this->createWindowButton('Cancelar')->closeWindow();
		$this->enableEvent('confirm');
	}
}