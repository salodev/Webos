<?php

namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\Visual\FormContainer;

class HtmlContainer extends Control {
	use FormContainer;
	
	public function div(string $text = ''): HtmlContainerNode {
		return $this->createTag('div', $text);
	}
	
	public function p(string $text = ''): HtmlContainerNode {
		return $this->createTag('p', $text);
	}
	
	public function h1(string $text = ''): HtmlContainerNode {
		return $this->createTag('h1', $text);
	}
	
	public function h2(string $text = ''): HtmlContainerNode {
		return $this->createTag('h2', $text);
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
	
	public function getAllowedActions(): array {
		return array_merge(parent::getAllowedActions(), ['scroll']);
	}
	
	public function getAvailableEvents(): array {
		return array_merge(parent::getAvailableEvents(), ['scroll']);
	}
	
	public function render(): string {
		$scrollTop  = $this->scrollTop  ?? 0;
		$scrollLeft = $this->scrollLeft ?? 0;
		
		return "<div class=\"HtmlContainers\" id=\"{$this->getObjectID()}\" {$this->getInlineStyle(true)} webos set-scroll-values=\"{$scrollTop},{$scrollLeft}\">" . ($this->content ?? '') . $this->getChildObjects()->render() . '</div>';
	}
	
	public function enableScroll(): self {
		$this->_attributes['overflowY'] = 'scroll';
		$this->_attributes['overflowX'] = 'hidden';
		return $this;
	}
	
	public function disableScroll(): self {
		unset($this->_attributes['overflowX']);
		unset($this->_attributes['overflowY']);
		return $this;
	}
	
	public function expand(): self {
		$this->top = 0;
		$this->left = 0;
		$this->right = 0;
		$this->bottom = 0;
		
		return $this;
	}
}