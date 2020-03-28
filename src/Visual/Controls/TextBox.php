<?php
namespace Webos\Visual\Controls;

use Webos\StringChar;
use Webos\Visual\KeysEvents;

class TextBox extends Field {
	
	/**
	 * Add key events behaviors
	 */
	use KeysEvents;

	public function render(): string {
		$html = '';
		
		/**
		 * Get all directives list.
		 */
		$directivesList = $this->getDirectivesList();
		
		/**
		 * Make string for rendering.
		 */
		$directives = count($directivesList) ? 'webos ' . implode(' ', $directivesList) : '';
		
		if ($this->multiline) {
			$html =  new StringChar(implode(' ', [
				'<textarea id="__id__"',
					'class="Control Field TextFieldControl"__style__ ',
					$directives,
					'placeholder="__placeholder__"',
					'__disabled__>__value__</textarea>',
			]));
		} else {
			$html =  new StringChar(implode(' ', [
				'<input id="__id__"',
					'class="Control Field TextFieldControl"__style__ ',
					'type="text"',
					'autocomplete="off"',
					$directives,
					'placeholder="__placeholder__"',
					'value="__value__"__disabled__ />',
			]));
		}
		
		$html->replaces([
			'__id__'            => $this->getObjectID(),
			'__value__'         => $this->value,
			'__placeholder__'   => $this->placeholder,
			'__style__'         => $this->getInlineStyle(),
			'__disabled__'      => $this->disabled ? ' disabled="disabled"' : '',
		]);

		return $html;
	}
	
	/**
	 * Easy way to get all available directives for current instace.
	 * Calling this method is easier because it deal with cases where is
	 * possible or not add some directives.
	 */
	public function getDirectivesList(): array {
		/**
		 * First, get all global keyEvents directives
		 */
		$directives = $this->getKeyEventsDirectives();
		
		/**
		 * However check if object can receive actions.
		 */
		if (!$this->isDisabled() && !$this->isHidden()) {
			
			/**
			 * Add directive for leaveTyping
			 */
			
			$directives[] = 'leavetyping';
			
			/**
			 * It allow catch any text key even if not focused
			 */
			if ($this->captureTyping()) {
				$directives[] = 'key-press-type';
			}
			
			/**
			 * Directive to autofocus
			 */
			if ( $this->hasFocus()) {
				$directives[] = 'focus';
			}
		}
		return $directives;
	}
	
	/**
	 * Creates an visual attached button to field.
	 * @param type $text
	 * @param type $width
	 * @return \Webos\Visual\Controls\Button
	 */
	public function attachButton(string $text = '...', int $width = 25): Button {
		return $this->getParent()->createButton($text, ['width' => $width]);
	}
	
	public function multiline(bool $value): self {
		$this->multiline = $value;
		return $this;
	}
	
	public function action_leaveTyping(array $params = []): void {
		if (empty($params['value'])) {
			// return;
		}
		$this->setValue($params['value']);
		$this->triggerEvent('leaveTyping', $params);
	}
	
	public function onLeaveTyping(callable $function, bool $persistent = true, array $context = []): self {
		$this->bind('leaveTyping', $function, $persistent, $context);
		return $this;
	}
}