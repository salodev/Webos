<?php
namespace Webos\Visual\Controls;
class TextBox extends Field {

	
	public function render() {
		$html = '';
		if ($this->multiline) {
			$html =  new \Webos\String(
				'<textarea id="__id__" ' .
					'class="TextFieldControl"__style__ ' .
					'onchange="__onchange__" ' .
					'placeholder="__placeholder__" ' .
					'name="__name__"__disabled__>__value__</textarea>'
			);
		} else {
			$html =  new \Webos\String(
				'<input id="__id__" ' .
					'class="TextFieldControl"__style__ ' .
					'type="text" ' .
					'onchange="__onchange__" ' .
					'name="__name__" ' .
					'placeholder="__placeholder__" ' .
					'value="__value__"__disabled__ />'
			);
		}
		$onchange = "__doAction('send',{actionName:'setValue',objectId:this.id, value:this.value});";

		$html->replaces(array(
			'__id__'          => $this->getObjectID(),
			'__onchange__'    => $onchange,
			'__name__'        => $this->name,
			'__value__'       => $this->value,
			'__placeholder__' => $this->placeholder,
			'__style__'       => $this->getInlineStyle(),
			'__disabled__'    => $this->disabled ? ' disabled="disabled"' : '',
		));

		return $html;
	}
}