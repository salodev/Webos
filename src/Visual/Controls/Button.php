<?php
namespace Webos\Visual\Controls;

use Webos\Visual\Control;
use Webos\StringChar;
use salodev\Deferred;
use salodev\Promise;

class Button extends Control {
	public function getAllowedActions(): array {
		return array(
			'click',
		);
	}

	public function getAvailableEvents(): array {
		return array(
			'click',
		);
	}

	public function click() {
		$this->triggerEvent('click');
	}
	
	/**
	 * 
	 * @param type $className
	 * @param array $parameters
	 * @return $this
	 */
	public function openWindow(string $className, array $parameters = []): self {
		$this->_openWindowClassName  = $className;
		$this->_openWindowParameters = $parameters;
		$this->onClick(function() {
			$this->getApplication()->openWindow($this->_openWindowClassName, $this->_openWindowParameters, $this->getParentWindow());
		});
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
			'<button id="__id__" class="__class__" type="button" name="__name__" webos click __style____disabled__>__value__</button>'
		);
		
		$html->replace('__style__', $this->getInLineStyle());
			
		$html->replaces(array(
			'__id__'      => $this->getObjectID(),
			'__class__'   => self::class,
			'__name__'    => $this->name,
			'__value__'   => $this->getChildObjects()->render() . $this->value,
			'__disabled__' => $this->disabled ? 'disabled="disabled"' : '',
		));

		return $html;
	}
}