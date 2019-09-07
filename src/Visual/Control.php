<?php
namespace Webos\Visual;

use Webos\Application;
use Webos\VisualObject;
use Exception;

abstract class Control extends VisualObject {

	final public function __construct(Application $application, VisualObject $parent, array $initialAttributes = []) {
		parent::__construct($application, $initialAttributes);

		$this->_parentObject = $parent;
		$parent->addChildObject($this);

		$this->initialize($initialAttributes);
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
	public function initialize(array $params = []) {}
	
	public function focus() {
		$this->getParentWindow()->setActiveControl($this);
	}
	
	public function hasFocus() {
		return $this->getParentWindow()->hasFocus($this);
	}
	
//	public function action(string $name, array $params = []) {
//		/**
//		 * Por razones de seguridad, si el objeto está deshabilitado,
//		 * se verifica y se frena la acción.
//		 */
//		if ($this->disabled == true) {
//			throw new Exception('Object disabled');
//		}
//		return parent::action($name, $params);
//	}

	public function click() {
		$this->triggerEvent('click');
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
	
	public function width(int $value): self {
		$this->width = $value;
		return $this;
	}
	
	public function height(int $value): self {
		$this->height = $value;
		return $this;
	}
	
	public function left(int $value): self {
		$this->left = $value;
		return $this;
	}
	
	public function right(int $value): self {
		$this->right = $value;
		return $this;
	}
	
	public function top(int $value): self {
		$this->top = $value;
		return $this;
	}
	
	public function bottom(int $value): self {
		$this->bottom = $value;
		return $this;
	}
	
	public function value($value): self {
		$this->value = $value;
		return $this;
	}
	
	public function action(string $name, array $params = []): void {
		parent::action($name, $params);
		$this->focus();
	}
}