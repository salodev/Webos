<?php
namespace Webos\Visual\Controls;
class PasswordBox extends Field {
	public function render(): string {
		$html = new \Webos\StringChar(
			'<input ' .
				'id="__id__" ' .
				'class="PasswordFieldControl"' .
				'__style__ ' .
				'type="password" ' .
				'name="__name__" ' .
				'onchange="__onchange__" ' .
				'value="__value__" ' .
			'/>'
		);
		$onchange = "__doAction('send',{actionName:'setValue',objectId:this.id, value:this.value});";
		
		return $html
			->replace('__id__',       $this->getObjectID())
			->replace('__name__',     $this->name)
			->replace('__onchange__', $onchange)
			->replace('__value__',    $this->value)
			->replace('__style__',    $this->getInlineStyle());
	}
}