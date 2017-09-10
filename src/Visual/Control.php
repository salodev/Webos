<?php
namespace Webos\Visual;

use Webos\Application;
use Webos\VisualObject;
use Exception;

abstract class Control extends VisualObject {

	final public function __construct(Application $application, VisualObject $parent, array $initialAttributes = array()) {
		parent::__construct($application, $initialAttributes);

		$this->_parentObject = $parent;
		$parent->addChildObject($this);

		$this->initialize();
	}

	/**
	 * La construcción del objeto ControlObject es delicada debido a la implementación
	 * del patrón relacional padre - hijo.
	 *
	 * Para asegurar ese comportamiento, el constructor no puede ser especializado
	 * y para permitir la inicialización de parámetros del objeto se dispone de un
	 * método initialize() que será invocado por el constructor, y que podrá ser
	 * especializado de acuerdo a las necesidades.
	 */
	public function initialize() {}
	
	public function focus() {
		$this->getParentWindow()->setActiveControl($this);
	}
	
	public function hasFocus() {
		return $this->getParentWindow()->hasFocus($this);
	}
	
	public function action(string $name, array $params = []) {
		/**
		 * Por razones de seguridad, si el objeto está deshabilitado,
		 * se verifica y se frena la acción.
		 */
		if ($this->disabled == true) {
			throw new Exception('Object disabled');
		}
		return parent::action($name, $params);
	}
	
	/**
	 * 
	 * @param $eventListener
	 * @return $this
	 */
	public function onClick(callable $eventListener, array $contextData = []): self {
		$this->bind('click', $eventListener, true, $contextData);
		return $this;
	}
	
	public function onFocus(callable $eventListener, array $contextData = []): self {
		$this->bind('foucs', $eventListener, true, $contextData);
		return $this;
	}
}