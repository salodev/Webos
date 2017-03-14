<?php
namespace Webos\Visual\Controls;
class Button extends \Webos\Visual\Control {
	private $_openWindowClassName  = null;
	private $_openWindowParameters = null;
	public function getAllowedActions() {
		return array(
			'press',
		);
	}

	public function getAvailableEvents() {
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
	public function openWindow($className, array $parameters = array()) {
		$this->_openWindowClassName  = $className;
		$this->_openWindowParameters = $parameters;
		$this->bind('press', array($this, 'onPressOpenWindow'));
		return $this;
	}
	
	public function onPressOpenWindow() {
		$this->getParentApp()->openWindow($this->_openWindowClassName, $this->_openWindowParameters, $this->getParentWindow());
	}
	
	public function render() {
		$html = new \Webos\String(
			'<input id="__id__" class="__class__" type="button" name="__name__" value="__value__" onclick="__onclick__"__style____disabled__ />'
		);
		
		$html->replace('__style__', $this->getInLineStyle());

		$onclick = new \Webos\String("__doAction('send', {actionName:'press', objectId:this.id});");
			
		$html->replaces(array(
			'__id__'      => $this->getObjectID(),
			'__class__'   => $this->getClassName(),
			'__name__'    => $this->name,
			'__value__'   => $this->value,
			'__onclick__' => $onclick,
			'__disabled__' => $this->disabled ? 'disabled="disabled"' : '',
		));

		return $html;
	}
}