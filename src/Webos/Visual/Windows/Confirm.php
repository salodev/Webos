<?php
namespace Webos\Visual\Windows;
use \Webos\Visual\Window;
use \Webos\Visual\Controls\Label;
class Confirm extends Window {

	public function  getInitialAttributes(): array {
		$attrs = parent::getInitialAttributes();

		return array_merge($attrs, array(
			'title' => 'Confirmar',
		));
	}

	public function initialize(array $params = []) {
		$this->enableEvent('confirm');
		$this->title = 'Confirmar';
		$this->height = 130;
	}

	public function getAllowedActions(): array {
		return array(
			'confirm',
			'close',
			'move',
		);
	}

	public function confirm() {
		$this->triggerEvent('confirm');
		$this->close();
	}
	
	public function render(): string {
		$template = $this->_getRenderTemplate();

		$content = new \Webos\StringChar(
			'<div __ALIGN__>' .
				'<div>MESSAGE</div>' .
				'<div style="margin-top:20px;">' .
					'<input type="button" value="Sí" webos action="confirm" />'.
					'<input type="button" value="no" webos action="close" />'.
				'</div>' .
			'</div>'
		);

		$align = 'style="text-align:center;"';
		if ($this->textAlign) {
			$align = 'style="text-align:'.$this->textAlign.';"';
		}
		$content->replace('__ALIGN__', $align);
		$content->replace('MESSAGE',   nl2br($this->message));
		$content->replace('OBJECTID',  $this->getObjectID());
		
		$template->replace('__CONTENT__', $content);

		return $template;
	}
}