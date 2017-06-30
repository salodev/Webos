<?php

namespace Webos\Visual\Controls;

class Frame extends \Webos\Visual\Control {

	public function setInitialAttributes(array $userAttrs = array()){
		$attrs = array(
			'width' => '400px',
			'height' => '100px',
		);

		$this->_attributes = array_merge($attrs, $userAttrs);
	}

	public function controls() {
		return $this->_childObjects;
	}

	public function getAllowedActions() {
		return array();
	}

	public function getAvailableEvents() {
		return array();
	}
	
	public function render() {
		$html = new \Webos\String('<div class="FrameControl"__style__>__content__</div>');

		$html->replace('__style__',  $this->getInlineStyle(true));
		$html->replace('__content__', $this->controls()->render());
		return $html;
	}
}