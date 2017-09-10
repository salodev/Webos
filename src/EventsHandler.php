<?php
namespace Webos;

use stdClass;
use Webos\Closure as WebosClosure;
use Closure;
use Exception;

class EventsHandler {

	public $events = null;
	protected $useAvailableEvents = null;
	protected $availableEvents = null;

	public function __construct() {
		$this->events = array();
		$this->availableEvents = array();
		$this->useAvailableEvents = false;
	}

	public function addListener(string $eventName, callable $eventListener, bool $persistent = true, array $contextData = []): self {

		$this->checkAvailableEvent($eventName);

		if (!is_callable($eventListener)) {
			throw new Exception('eventListener must be a function or an Closure instance');
		}
		
		$dependenciesList = $this->_getDependenciesList($eventListener);
		
		if ($eventListener instanceof Closure) {
			$eventListener = new WebosClosure($eventListener);
		}
		
		$evData = new stdClass();
		$evData->eventListener    = $eventListener;
		$evData->persistent       = (bool) $persistent;
		$evData->contextData      = $contextData;
		$evData->dependenciesList = $dependenciesList;

		$this->events[$eventName][] = $evData;

		return $this;
	}

	public function removeListeners(string $eventName){
		//@todo: completar esto..
		throw new Exception('TODO: complete it.');
	}

	public function trigger(string $eventName, $source, $params = null): bool {

		$this->checkAvailableEvent($eventName);

		if(isset($this->events[$eventName])) {			
			foreach ($this->events[$eventName] as $k => $evData) {
				if (!$evData->persistent) {
					unset($this->events[$eventName][$k]);
				}
				
				$dependencies = $this->_buildDependenciesArray($evData->dependenciesList, [
					'source'      => $source,
					'eventName'   => $eventName,
					'params'      => $params,
					'parameters'  => $params,
					'data'        => $params,
					'contextData' => $evData->contextData,
					'context'     => $evData->contextData,
				]);
				
				$return = call_user_func_array($evData->eventListener, $dependencies);

				// Si un eventListener response con FALSE, significa que está solicitando
				// la detención de ejecución de los eventos, y dicho valor será entregado
				// como resultado a quien ejecuta el método trigger, para que éste decida
				// cómo actuar en ese caso.
				if ($return === false) {
					return false;
				}
			}			
		}
		
		return true;
	}
	
	public function isAvailable(string $eventName): bool {
		if ($this->useAvailableEvents) {
			if (!in_array($eventName, $this->availableEvents)) {
				return false;
			}
		}
		return true;
	}

	private function checkAvailableEvent(string $eventName) {
		if (!$this->isAvailable($eventName)) {
			throw new Exception("Unavailable {$eventName} event.");
		}
	}

	public function setAvailableEvents(array $eventsList){
		$this->availableEvents = array_merge($this->availableEvents, $eventsList);
		$this->useAvailableEvents = true;
	}
	
	public function enableEvent(string $eventName) {
		if (!in_array($eventName, $this->availableEvents)) {
			$this->availableEvents[] = $eventName;
		}
	}

	public function getAvailableEvents(): array {
		return $this->availableEvents;
	}
	
	public function hasListenersForEventName(string $eventName): bool {
		if (!isset($this->events[$eventName])) {
			return false;
		}
		return count($this->events[$eventName])>0;
	}
	
	private function _getDependenciesList(callable $fn) {
		$di = new DependencyInjector();
		return $di->getDependenciesList($fn);
	}
	
	private function _buildDependenciesArray(array $list, array $dependencies): array {
		$di = new DependencyInjector();
		return $di->buildDependenciesFromArray($list, $dependencies);
	}
}