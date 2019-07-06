<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\StringChar;

class Button extends Control {
	public function getAllowedActions(): array {
		return [
			'click',
		];
	}

	public function getAvailableEvents(): array {
		return [
			'click',
		];
	}
	
	/**
	 * 
	 * @param type $className
	 * @param array $parameters
	 * @return $this
	 */
	public function openWindow(string $className, array $parameters = []): self {
		$this->onClick(function($context) {
			$this->getApplication()->openWindow($context['className'], $context['parameters'], $this->getParentWindow());
		}, [
			'className' => $className,
			'parameters' => $parameters
		]);
		return $this;
	}
	
	/**
	 * 
	 * @return $this
	 */
	public function closeWindow(): self {
		$this->onClick(function() {
			$this->getParentWindow()->close();
		});
		return $this;
	}
	
	public function render(): string {
		
		if ($this->visible === false) {
			return '';
		}
		
		$html = new StringChar(
			'<button id="__id__" class="Control __class__" type="button" name="__name__" webos click __style____disabled__>__value__</button>'
		);
		
		$html->replace('__style__', $this->getInLineStyle());
			
		$html->replaces([
			'__id__'      => $this->getObjectID(),
			'__class__'   => $this->getClassNameForRender(),
			'__name__'    => $this->name,
			'__value__'   => $this->getChildObjects()->render() . $this->value,
			'__disabled__' => $this->disabled ? 'disabled="disabled"' : '',
		]);

		return $html;
	}
}