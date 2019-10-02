<?php

namespace Webos\Visual\BootstrapUI\Form;
use Webos\StringChar;

class Text extends Labeled {
	protected $attrType = 'text';
	
	public function getTemplate(): string {
		$id = $this->getObjectID();
		return "<input 
			type=\"{$this->attrType}\" 
				class=\"form-control\" 
				id=\"{$id}\" 
				aria-describedby=\"{$id}-help\" 
				placeholder=\"{$this->placeholder}\"
				value=\"{$this->value}\"
				webos leavetyping />";
	}
	
	public function action_leaveTyping(array $params = []): void {
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
}
