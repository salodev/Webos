<?php
namespace Webos\Visual\Windows;

use Webos\Visual\Window;

class Message extends Window {

	public function initialize(array $params = []) {
		$this->title       = $params['title'  ] ?? 'Mensaje:';
		$this->text        = $params['message'] ?? 'Mensaje:';
		$this->width       = $params['width'  ] ?? 450;
		$this->messageType = $params['type'   ] ?? null;
		$this->modal = true;
		
		$this->createWindowButton('OK')->closeWindow();
		$this->height      = $params['height' ] ?? 150;

	}

	public function  getInitialAttributes(): array {
		return [
			'height' => 100,
			'width'  => 200
		];
	}
}