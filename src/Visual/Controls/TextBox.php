<?php
namespace Webos\Visual\Controls;

use Webos\StringChar;

class TextBox extends Field {

	public function render(): string {
		$html = '';
		if ($this->multiline) {
			$html =  new StringChar(
				'<textarea id="__id__" ' .
					'class="TextFieldControl"__style__ ' .
					'webos leavetyping __focus__ __captureTyping__ ' .
					'placeholder="__placeholder__" ' .
					'name="__name__"__disabled__>__value__</textarea>'
			);
		} else {
			$html =  new StringChar(
				'<input id="__id__" ' .
					'class="TextFieldControl"__style__ ' .
					'type="text" ' .
					'webos leavetyping __focus__ __captureTyping__ ' .
					'name="__name__" ' .
					'placeholder="__placeholder__" ' .
					'value="__value__"__disabled__ />'
			);
		}
		
		$hasLeaveTypingEvent = $this->_eventsHandler->hasListenersForEventName('leaveTyping');
		$html->replaces(array(
			'__id__'          => $this->getObjectID(),
			'__name__'        => $this->name,
			'__value__'       => $this->value,
			'__placeholder__' => $this->placeholder,
			'__style__'       => $this->getInlineStyle(),
			'__disabled__'    => $this->disabled ? ' disabled="disabled"' : '',
			'__leavetyping__' => $hasLeaveTypingEvent ? ' leavetyping' : '',
			'__focus__'       => $this->hasFocus() ? 'focus' : '',
			' __captureTyping__'=> $this->captureTyping() ? 'capture-typing' : '',
		));

		return $html;
	}
	
	public function multiline(bool $value): self {
		$this->multiline = $value;
		return self;
	}
	
	public function setValue($mixed) {
		if ($this->disabled) { return; }
		parent::setValue($mixed);
	}
	
	public function leaveTyping(array $params) {
		if (empty($params['value'])) {
			// return;
		}
		$this->setValue($params['value']);
		$this->triggerEvent('leaveTyping', $params);
	}
	
	public function onLeaveTyping(callable $callback): self {
		$this->bind('leaveTyping', $callback);
		return $this;
	}
	
	public function getAvailableEvents(): array {
		return array_merge(parent::getAvailableEvents(), ['leaveTyping']);
	}
	
	public function getAllowedActions(): array {
		return array_merge(parent::getAllowedActions(), ['leaveTyping']);
	}
}