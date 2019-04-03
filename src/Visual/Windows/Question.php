<?php
namespace Webos\Visual\Windows;

use Webos\Visual\Window;

class Question extends Window {

	public function initialize(array $params = []) {		
		$this->enableEvent('confirm');
		$this->title       = $params['title'  ] ?? 'Question:';
		$this->text        = $params['message'] ?? 'Mensaje:';
		$this->width       = $params['width'  ] ?? 450;
		$this->height      = $params['height' ] ?? 150;
		$this->messageType = $params['type'   ] ?? null;
	}
	
	public function ifAnswer(string $text, callable $fn): self {
		$this->createWindowButton($text)->onClick($fn)->onClick(function() {
			$this->close();
		});
		return $this;
	}

	public function  getInitialAttributes(): array {
		return array(
			'height' => 100,
			'width'  => 200
		);
	}
}