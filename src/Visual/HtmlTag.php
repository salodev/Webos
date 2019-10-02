<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Webos\Visual;

/**
 * Description of HtmlRender
 *
 * @author salomon
 */
class HtmlTag {
	
	//put your code here
	
	private $tagName    = null;
	private $attributes = [];
	private $content    = null;
	public  $autoclose  = true;
	
	public function __construct(string $tagName, array $attributes = []) {
		$this->tagName    = $tagName;
		$this->attributes = $attributes;
	}
	
	public function setContent(string $content): self {
		$this->content = $content;
		return $this;
	}
	
	public function render(): string {
		$html = '';
		$html .= "<{$this->tagName} ";
		
		$attributes = [];
		foreach($this->attributes as $name => $value) {
			if ($value) {
				$attributes[] = "{$name}=\"{$value}\"";
			} else {
				$attributes[] = $name;
			}
		}
		
		$html .= implode(' ', $attributes);
		
		if (empty($this->content)) {
			if ($this->autoclose) {
				$html .= " />";
			} else {
				$html .= "></{$this->tagName}>";
			}
		} else {
			$html .= '>';
			$html .= $this->content;
			$html .= "</{$this->tagName}>";
		}
		
		return $html;
	}
	
	public function setAttribute($name, $value): self {
		$this->attributes[$name] = $value;
		return $this;
	}
	
	public function __toString(): string {
		return $this->render();
	}
}
