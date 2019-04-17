<?php
namespace Webos\Visual\Controls;

use Webos\StringChar;
use salodev\FileSystem\File;

class FilePicker extends Field {

	public function render(): string {
		$html =  new StringChar(
			'<div id="__id__" webos filepicker>' . 
				'<form action="" target="__id__-iframe" method="post" enctype="multipart/form-data">' .
					'<input type="hidden" name="actionName" value="fileUpload" />' .
					'<input type="hidden" name="objectID" value="__id__" />' .
					'<input ' .
						'class="__class__"__style__ ' .
						'type="file" ' .
						'autocomplete="off" ' .
						'webos __focus__ ' .
						'name="file" ' .				
						'__disabled__ />' .
				'</form>' .
				'<iframe name="__id__-iframe" frameborder="0" style="display:none;"></iframe>' .
			'</div>'
		);
		
		$hasLeaveTypingEvent = $this->_eventsHandler->hasListenersForEventName('leaveTyping');
		$html->replaces(array(
			'__id__'          => $this->getObjectID(),
			'__value__'       => $this->value,
			'__placeholder__' => $this->placeholder,
			'__style__'       => $this->getInlineStyle(),
			'__disabled__'    => $this->disabled ? ' disabled="disabled"' : '',			
			'__focus__'       => $this->hasFocus() ? 'focus' : '',
			'__class__'       => $this->getClassNameForRender(),
		));

		return $html;
	}
	
	public function enabledTypes(array $types): self {
		$this->restrictionMode = 'enabled';
		$this->types = $types;
		return $this;
	}
	
	public function denyTypes(array $types): self {
		$this->restrictionMode = 'deny';
		$this->types = $types;
		return $this;
	}
	
	public function checkType($type): bool {
		if ($this->restrictionMode == 'enabled') {
			if (!in_array($type, $this->types)) {
				return false;
			}
		}
		if ($this->restrictionMode == 'deny') {
			if (in_array($type, $this->types)) {
				return false;
			}
		}
		return true;
	}
	
	public function fileUpload(array $data = []) {
		$file = new File($data['__uploadedFile']);
		$mimeType = $file->getMimeType();
		
		if (!$this->checkType($mimeType)) {
			throw new \Exception('File type is not allowed');
		}
		
		$this->triggerEvent('fileUpload', [
			'file' => $file,
		]);
	}
	
	/**
	 * Callback function for file upload.
	 * Usage:
	 * 
	 * $filePicker->onUpload(function($data) {
	 *		$data['file']->getAllContent();
	 * });
	 * @param \Webos\Visual\Controls\callable $fn
	 * @return \self
	 */
	public function onUpload(callable $fn): self {
		$this->bind('fileUpload', $fn);
		return $this;
	}
	
	public function getAvailableEvents(): array {
		return array_merge(parent::getAvailableEvents(), ['fileUpload']);
	}
	
	public function getAllowedActions(): array {
		return array_merge(parent::getAllowedActions(), ['fileUpload']);
	}
}