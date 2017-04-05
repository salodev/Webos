<?php
namespace Webos\Visual;
use \salodev\Utils;
class MessageWindow extends Window {

	public function initialize(array $params = array()) {
		$this->title   = Utils::Ifnull($params['title'  ], 'Mensaje:');
		$this->message = Utils::Ifnull($params['message'], 'Mensaje:');
		$this->width   = Utils::Ifnull($params['width'  ], '450px'   );
		$this->height  = Utils::Ifnull($params['height' ], '150px'   );
		$this->messageType = Utils::Ifnull($params['type']);

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
				'<div style="margin:10px 20px 20px 20px;font-weight:bold;__MESSAGE_TYPE__">__MESSAGE__</div>' .
				'<div>' .
					'<input type="button" value="Cerrar" onclick="__ONCLICK__" />'.
				'</div>' .
			'</div>'
		);
		
		$messageType = 'color:blue;';
		if ($this->messageType=='info') {
			$messageType = 'color:blue;';
		}
		if ($this->messageType=='error') {
			$messageType = 'color:red;';
		}
		
		$onClick = "__doAction('send',{actionName:'close', objectId:'__OBJECTID__'});";
		$content->replace('__MESSAGE__',      $this->message      );
		$content->replace('__MESSAGE_TYPE__', $messageType        );
		$content->replace('__ONCLICK__',      $onClick            );
		$content->replace('__OBJECTID__',     $this->getObjectID());
		
		$template->replace('__TITLE__',       $this->title        );
		$template->replace('__CONTENT__',     $content            );
		
		return $template;
	}
}