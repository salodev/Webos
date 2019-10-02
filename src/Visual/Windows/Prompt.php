<?php
namespace Webos\Visual\Windows;
use Webos\Visual\Window;
use Webos\Visual\Controls\TextBox;
class Prompt extends Window {

	public function initialize(array $params = []) {
		$this->title  = $this->message;
		$this->height = 125;
		$this->width  = 327;
		$this->modal  = true;
		$this->textBox = $textBox = $this->createObject(TextBox::class, [
			'width'=>'100%',
			'bottom' => 30,
		]);
		
		if (!empty($params['defaultValue'])) {
			$textBox->value = $params['defaultValue'];
		}
		
		// $this->height = 130;
		
		$this->createWindowButton('Confirmar')->onClick(function() {
			$this->close();
			$this->triggerEvent('confirm', [
				'value' => $this->textBox->value,
			]);
		});
		$this->createWindowButton('Cancelar')->closeWindow();
		$this->height = 130;
		$this->textBox->focus();
	}
}