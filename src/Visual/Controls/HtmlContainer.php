<?php

namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\Visual\FormContainer;
use Webos\Visual\Controls\HtmlContainerNode;

class HtmlContainer extends Control {
	use FormContainer;
	
	public function p(string $text = ''): HtmlContainerNode {
		return $this->createTag('p', $text);
	}
	
	public function h1(string $text = ''): HtmlContainerNode {
		return $this->createTag('h1', $text);
	}
	
	public function blockquote(string $text = ''): HtmlContainerNode {
		return $this->createTag('blockquote', $text);
	}
	
	public function a(string $text = ''): HtmlContainerNode {
		return $this->createTag('a', $text);
	}
	public function br(string $text = ''): HtmlContainerNode {
		return $this->createTag('br', $text);
	}
	
	public function createTag(string $tagName, string $text = ''): HtmlContainerNode {
		return $this->createObject(HtmlContainerNode::class, [
			'tagName' => $tagName,
			'text'    => $text,
		]);
	}
	
	public function scroll() {
		$this->triggerEvent('scroll');
	}
	
	public function getAllowedActions(): array {
		return array_merge(parent::getAllowedActions(), ['scroll']);
	}
	
	public function render(): string {
		$scrollTop  = $this->scrollTop  ?? 0;
		$scrollLeft = $this->scrollLeft ?? 0;
		return "<div {$this->getInlineStyle(true)} webos set-scroll-values=\"{$scrollTop},{$scrollLeft}\">" . $this->getChildObjects()->render() . '</div>';
	}
}