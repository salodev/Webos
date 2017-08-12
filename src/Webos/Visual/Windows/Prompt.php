<?php
namespace Webos\Visual\Windows;
use \Webos\Visual\Window;
class Prompt extends Window {

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
		$t = $this->createTextBox($this->message, 'promptText');
		if (!empty($params['defaultValue'])) {
			$t->value = $params['defaultValue'];
		}
	}

	public function getAllowedActions(): array {
		return array(
			'confirm',
			'close',
			'move',
		);
	}

	public function confirm() {
		$this->triggerEvent('confirm', [
			'value' => $this->promptText->value,
		]);
		$this->close();
	}
	
	public function render(): string {
		$template = $this->_getRenderTemplate();

		$content = new \Webos\StringChar(
			'<div style="text-align:center;">' .
				'<div>__CONTENT__</div>' .
				'<div style="margin-top:20px;">' .
					'<input type="button" value="Sí" webos action="confirm" />'.
					'<input type="button" value="no" webos action="close" />'.
				'</div>' .
			'</div>'
		);

		$content->replace('__CONTENT__', $this->getChildObjects()->render());
		$content->replace('OBJECTID',  $this->getObjectID());
		
		$template->replace('__CONTENT__', $content);

		return $template;
	}
}