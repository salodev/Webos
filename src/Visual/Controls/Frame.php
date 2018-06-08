<?php

namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\Visual\FormContainer;
use Webos\ObjectsCollection;
use Webos\StringChar;

class Frame extends Control {
	use FormContainer;

	public function setInitialAttributes(array $userAttrs = array()){
		$attrs = array(
			'top'    => 0,
			'right'  => 0,
			'bottom' => 0,
			'left'   => 0,
		);

		$this->_attributes = array_merge($attrs, $userAttrs);
	}

	public function getControls(): ObjectsCollection {
		return $this->_childObjects;
	}

	public function getAllowedActions(): array {
		return array();
	}

	public function getAvailableEvents():array  {
		return array();
	}
	
	public function render(): string {
		$html = new StringChar('<div class="FrameControl"__style__>__content__</div>');

		$html->replace('__style__',  $this->getInlineStyle(true));
		$html->replace('__content__', $this->getChildObjects()->render());
		return $html;
	}
}