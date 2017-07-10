<?php
namespace Webos\Visual\Controls;
class Button extends \Webos\Visual\Control {
	public function getAllowedActions(): array {
		return array(
			'press',
		);
	}

	public function getAvailableEvents(): array {
		return array(
			'press',
		);
	}

	public function press() {
		$this->triggerEvent('press');
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
		$this->onPress(function() {
			$this->getApplication()->openWindow($this->_openWindowClassName, $this->_openWindowParameters, $this->getParentWindow());
		});
		return $this;
	}
	
	/**
	 * 
	 * @return $this
	 */
	public function closeWindow(): self {
		$this->onPress(function() {
			$this->getParentWindow()->close();
		});
		return $this;
	}
	
	/**
	 * 
	 * @param \Webos\Visual\Controls\callable $eventListener
	 * @return $this
	 */
	public function onPress(callable $eventListener): self {
		$this->bind('press', $eventListener);
		return $this;
	}
	
	public function render(): string {
		$html = new \Webos\StringChar(
			'<button id="__id__" class="__class__" type="button" name="__name__" onclick="__onclick__"__style____disabled__>__value__</button>'
		);
		
		$html->replace('__style__', $this->getInLineStyle());

		$onclick = new \Webos\StringChar("__doAction('send', {actionName:'press', objectId:this.id});");
			
		$html->replaces(array(
			'__id__'      => $this->getObjectID(),
			'__class__'   => self::class,
			'__name__'    => $this->name,
			'__value__'   => $this->getChildObjects()->render() . $this->value,
			'__onclick__' => $onclick,
			'__disabled__' => $this->disabled ? 'disabled="disabled"' : '',
		));

		return $html;
	}
}