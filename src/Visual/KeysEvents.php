<?php

namespace Webos\Visual;

trait KeysEvents {	
	
	protected function _getKeysForEvents(): array {
		return [
			'Escape','Enter',
			'F1','F2','F3','F4','F5','F6','F7','F8','F9','F10','F11','F12',
			'ArrowUp','ArrowDown','ArrowLeft','PageDown','PageUp',
			'Home','End','Insert','Delete',
			'Shift','Control','Alt',			
		];
	}
	
	public function onKeyPress(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPress', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyEscape(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressEscape', $function, $persistent, $context);
		return $this;
	}
	public function onKeyF1(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF1', $function, $persistent, $context);
		return $this;
	}
	public function onKeyF5(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF5', $function, $persistent, $context);
		return $this;
	}
	public function onKeyPageDown(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressPageDown', $function, $persistent, $context);
		return $this;
	}
	
	
}