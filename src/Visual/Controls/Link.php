<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\StringChar;

/**
 * Description of Link
 *
 * @author salomon
 */
class Link extends Control {
	//put your code here
	
	public function initialize(array $params = []) {
		$this->enableEvent('click');
	}
	
	public function getRequiredParams(): array {
		return array_merge(parent::getRequiredParams(), ['url']);
	}
	
	public function getAllowedActions(): array {
		return ['click'];
	}
	
	public function render(): string {
		if ($this->hasListenerFor('click')) {
			$html = new StringChar(
				'<a id="__id__" href="" class="Control __class__" webos click __style____disabled__>__text__</a>'
			);

		} else {
			$html = new StringChar("<a class=\"Control __class__\" href=\"{$this->url}\" __style__ >__text__</a>");
		}

			$html->replace('__style__', $this->getInLineStyle());

			$html->replaces([
				'__id__'      => $this->getObjectID(),
				'__class__'   => $this->getClassNameForRender(),
				'__text__'   => $this->getChildObjects()->render() . $this->text,
				'__disabled__' => $this->disabled ? 'disabled="disabled"' : '',
				'__style__' => $this->getInlineStyle(true),
			]);
			
			return $html;
	}
}
