<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\StringChar;

class LinkButton extends Control {
	
	public function getRequiredParams(): array {
		return array_merge(parent::getRequiredParams(), ['url']);
	}
	
	public function render(): string {
		
		if ($this->visible === false) {
			return '';
		}
		
		$html = new StringChar(
			'<a href="__url__" id="__id__" class="Control __class__" __style____disabled__>__value__</a>'
		);
		
		$html->replace('__style__', $this->getInLineStyle());
		
		$url = $this->url;
		$text = $this->text ?? $this->url;
			
		$html->replaces([
			'__id__'      => $this->getObjectID(),
			'__class__'   => $this->getClassNameForRender(),
			'__name__'    => $this->name,
			'__url__'     => $url,
			'__value__'   => $text,
			'__disabled__' => $this->disabled ? 'disabled="disabled"' : '',
		]);

		return $html;
	}
}