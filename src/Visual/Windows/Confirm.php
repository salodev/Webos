<?php
namespace Webos\Visual\Windows;

use Webos\Visual\Window;
use Webos\Visual\Controls\Label;

class Confirm extends Window {

	public function initialize(array $params = []) {
		$this->enableEvent('confirm');
		$this->title = 'Confirmar';
		$t = $this->createToolBar([
			'fixedTo' => 'bottom',
			'horizontalAlign' => 'right',
		]);
		$this->height = 130;
		$this->text = $this->message;
		$t->addButton('Confirmar')->onClick(function() {
			$this->triggerEvent('confirm');
			$this->close();
		});
		$t->addButton('Cancelar')->closeWindow();
		$this->enableEvent('confirm');
	}
}