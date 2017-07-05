<?php
namespace Webos\Visual\Windows;
use \Webos\Visual\Window;
class Prompt extends Window {

	public function  getInitialAttributes() {
		$attrs = parent::getInitialAttributes();

		return array_merge($attrs, array(
			'title' => 'Confirmar',
		));
	}

	public function initialize() {
		$this->title = 'Confirmar';
		$this->height = '130px';
		$this->createTextBox($this->message, 'promptText');
	}

	public function getAllowedActions() {
		return array(
			'confirm',
			'close',
			'move',
		);
	}

	public function  getAvailableEvents() {
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

	public function close() {
		$this->triggerEvent('close');
		$this->getParentApp()->closeWindow($this);
	}
	
	public function render() {
		$template = $this->_getRenderTemplate();

		$content = new \Webos\String(
			'<div style="text-align:center;">' .
				'<div>__CONTENT__</div>' .
				'<div style="margin-top:20px;">' .
					'<input type="button" value="Sí" onclick="CLICK_YES" />'.
					'<input type="button" value="no" onclick="CLICK_NO" />'.
				'</div>' .
			'</div>'
		);

		$content->replace('__CONTENT__', $this->getChildObjects()->render());
		$content->replace('CLICK_YES', "__doAction('send',{actionName:'confirm', objectId:'OBJECTID'});");
		$content->replace('CLICK_NO',  "__doAction('send',{actionName:'close', objectId:'OBJECTID'});");
		$content->replace('OBJECTID',  $this->getObjectID());
		
		$template->replace('__CONTENT__', $content);

		return $template;
	}
}