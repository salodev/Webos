<?php
namespace Webos\Visual\Controls;

use Webos\StringChar;

class PasswordBox extends TextBox {
	public function render(): string {
		$html = new StringChar(
			'<input ' .
				'id="__id__" ' .
				'class="Control Field PasswordFieldControl"' .
				'__style__ ' .
				'type="password" ' .
				'name="__name__" ' .
				'webos leavetyping ' .
				'value="__value__" ' .
			'/>'
		);
		
		return $html
			->replace('__id__',       $this->getObjectID())
			->replace('__name__',     $this->name)
			->replace('__value__',    $this->value)
			->replace('__style__',    $this->getInlineStyle());
	}
}