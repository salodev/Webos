<?php
namespace Webos\Visual\Controls;
class PasswordField extends Field {
	public function render() {
		$html = new \Webos\String(
			'<input class="PasswordFieldControl"__style__ type="password" name="__name__" value="__value__" />'
		);

		return $html
			->replace('__name__',  $this->name)
			->replace('__value__', $this->value)
			->replace('__style__', $this->getInlineStyle());
	}
}