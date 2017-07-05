<?php
namespace Webos\Visual\Controls;
class TextBox extends Field {

	
	public function render() {
		$html = '';
		if ($this->multiline) {
			$html =  new \Webos\String(
				'<textarea id="__id__" ' .
					'class="TextFieldControl__leaveTyping__"__style__ ' .
					'onchange="__onchange__" ' .
					'placeholder="__placeholder__" ' .
					'name="__name__"__disabled__>__value__</textarea>'
			);
		} else {
			$html =  new \Webos\String(
				'<input id="__id__" ' .
					'class="TextFieldControl__leaveTyping__"__style__ ' .
					'type="text" ' .
					'onchange="__onchange__" ' .
					'name="__name__" ' .
					'placeholder="__placeholder__" ' .
					'value="__value__"__disabled__ />'
			);
		}
		$onchange = "__doAction('send',{actionName:'setValue',objectId:this.id, value:this.value});";
		$hasLeaveTypingEvent = $this->_eventsHandler->hasListenersForEventName('leaveTyping');
		$html->replaces(array(
			'__id__'          => $this->getObjectID(),
			'__onchange__'    => $onchange,
			'__name__'        => $this->name,
			'__value__'       => $this->value,
			'__placeholder__' => $this->placeholder,
			'__style__'       => $this->getInlineStyle(),
			'__disabled__'    => $this->disabled ? ' disabled="disabled"' : '',
			'__leaveTyping__' => $hasLeaveTypingEvent ? ' leaveTyping' : '',
		));

		return $html;
	}
	
	public function leaveTyping($value) {
		$this->setValue($value);
		$this->triggerEvent('leaveTyping');
	}
	
	public function onLeaveTyping(callable $callback) {
		$this->bind('leaveTyping', $callback);
		return $this;
	}
	
	public function getAvailableEvents() {
		return array_merge(parent::getAvailableEvents(), ['leaveTyping']);
	}
	
	public function getAllowedActions() {
		return array_merge(parent::getAllowedActions(), ['leaveTyping']);
	}
}