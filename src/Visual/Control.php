<?php
namespace Webos\Visual;

use Webos\Application;
use Webos\VisualObject;
use Exception;

abstract class Control extends VisualObject {
	
	public function focus() {
		$this->getParentWindow()->setActiveControl($this);
	}
	
	public function hasFocus() {
		return $this->getParentWindow()->hasFocus($this);
	}
	
	public function action_click(): void {
		$this->click();
	}

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