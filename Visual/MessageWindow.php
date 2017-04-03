<?php
namespace Webos\Visual;
class MessageWindow extends Window {

	public function initialize(array $params = array()) {
		$this->title = \salodev\Utils::Ifnull($params['title'], 'Mensaje:');
		$message = \salodev\Utils::Ifnull($params['message'], 'Mensaje:');
		$this->createObject('\Webos\Visual\Controls\Label', array(
			'text' => $message,
			'height' => $this->height,
		));

	}

	public function  getInitialAttributes() {
		return array(
			'height' => '100px',
			'width'  => '200px'
		);
	}
	public function  getAllowedActions() {
		return array(
			'close',
			'move'
		);
	}

	public function  getAvailableEvents() {
		return array(
			'close',
			'move'
		);
	}

	public function close() {
		$this->triggerEvent('close');
		$this->getParentApp()->closeWindow($this);
	}
	
	public function render() {
		$template = $this->_getRenderTemplate();

		$content = new \Webos\String(
			'<div style="text-align:center;">' .
				'<div>MESSAGE<br /></div>' .
				'<div>' .
					'<input type="button" value="Cerrar" onclick="ONCLICK" />'.
				'</div>' .
			'</div>'
		);
		
		$content->replace('MESSAGE', $this->message);
		$content->replace('ONCLICK', "__doAction('send',{actionName:'close', objectId:'OBJECTID'});");
		$content->replace('OBJECTID', $this->getObjectID());

		$template->replace('__TITLE__', $this->title);
		$template->replace('__CONTENT__', $content);
		
		return $template;
	}
}