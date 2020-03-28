<?php

namespace Webos\Visual;

trait KeysEvents {	

	/**
	 * Define a set of supported keys. Useful for validations
	 * @return array
	 */
	protected function _getKeysForEvents(): array {
		return [
			'Escape','Enter',
			'F1','F2','F3','F4','F5','F6','F7','F8','F9','F10','F11','F12',
			'ArrowUp','ArrowDown','ArrowLeft','ArrowRight','PageDown','PageUp',
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
	
	public function onKeyEnter(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressEnter', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyF1(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF1', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyF2(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF2', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyF3(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF3', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyF4(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF4', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyF5(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF5', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyF6(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF6', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyF7(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF7', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyF8(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF8', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyF9(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF9', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyF10(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF10', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyF11(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF11', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyF12(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressF12', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyPageUp(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressPageUp', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyPageDown(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressPageDown', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyArrowUp(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressArrowUp', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyArrowDown(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressArrowDown', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyArrowLeft(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressArrowLeft', $function, $persistent, $context);
		return $this;
	}
	
	public function onKeyArrowRight(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('keyPressArrowRight', $function, $persistent, $context);
		return $this;
	}
	
	/**
	 * Add the one-way receiver method for all user key events.
	 */
	public function action_keyPress(array $params): void {
		/**
		 * Must specify the key
		 */
		if (empty($params['key'])) {
			throw new \Exception('Missing key param');
		}
		
		$keyName = $params['key'];
		
		/**
		 * And sure supported
		 */
		if (!in_array($keyName, $this->_getKeysForEvents())) {
			throw new \Exception('Invalid key name');
		}
		
		/**
		 * First notify about any keypress.
		 */
		$this->triggerEvent('keyPress', [
			'key' => $params['key'],
		]);
		
		/**
		 * And for especific key event.
		 */
		$this->triggerEvent("keyPress{$keyName}", $params);
	}
	
	public function getKeyEventsDirectives(): array {
		$keys = [];
		$directives = [];
		if (!$this->isDisabled() && !$this->isHidden()) {
			foreach($this->_getKeysForEvents() as $keyName) {
				if ($this->hasListenerFor("keyPress{$keyName}")) {
					$keys[] = $keyName;
				}
			}
			if (count($keys)){
				$directives[] = 'key-press="' . implode(',', $keys) . '"';
			}
		}
		
		return $directives;
	}
	
	
}