<?php
namespace Webos\Visual\Windows;
use Webos\Visual\Window;
use Webos\Visual\Controls\TextBox;
class Prompt extends Window {

	public function initialize(array $params = []) {
		$this->title = $this->message;
		$this->height = 25;
		$this->width = 327;
		$this->textBox = $textBox = $this->createObject(TextBox::class, [
			'width'=>'100%',
		]);
		
		if (!empty($params['defaultValue'])) {
			$textBox->value = $params['defaultValue'];
		}
		
		$buttonsBar = $this->createButtonsBar();
		
		// $this->height = 130;
		
		$buttonsBar->addButton('Confirmar')->onClick(function() {
			$this->close();
			$this->triggerEvent('confirm', [
				'value' => $this->textBox->value,
			]);
		});
		$buttonsBar->addButton('Cancelar')->closeWindow();
		$this->enableEvent('confirm');
	}
}