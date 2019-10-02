<?php

namespace Webos\Visual\Controls;

use Webos\Visual\HtmlTag;

class CheckBox extends Field {
	
	public function initialize(array $params = []) {
		parent::initialize($params);
		$this->width = null;
	}
	
	public function render(): string {
		$tag = new HtmlTag('input', [
			'type'  => 'checkbox',
			'id'    => $this->getObjectID(),
			'webos' => null,
			'click' => null,
			'style' => $this->getInlineStyle(true, false),
		]);
		
		if ($this->checked) {
			$tag->setAttribute('checked', 'checked');
		}
		
		return $tag->render();
	}
	
	public function action_click(): void {
		if ($this->checked) {
			$this->checked = false;
		} else {
			$this->checked = true;
		}
		parent::action_click();
	}
}