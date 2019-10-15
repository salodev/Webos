<?php
namespace Webos;

use stdClass;
use Webos\Closure as WebosClosure;
use Closure;
use Exception;

class EventsHandler {

	private $events = [];

	public function addListener(string $eventName, callable $eventListener, bool $persistent = true, array $contextData = []): self {

		if (!is_callable($eventListener)) {
			throw new Exception('eventListener must be a function or an Closure instance');
		}
		
		$dependenciesList = DependencyInjector::getDependenciesList($eventListener);
		
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
		if (!isset($this->events[$eventName])) {
			throw new \Exception('Event not listed');
		}
		unset($this->events[$eventName]);
		return $this;
	}

	public function trigger(string $eventName, $source, $params = null): bool {

		if(isset($this->events[$eventName])) {			
			foreach ($this->events[$eventName] as $k => $evData) {
				if (!$evData->persistent) {
					unset($this->events[$eventName][$k]);
				}
				
				$dependencies = DependencyInjector::buildDependenciesFromArray($evData->dependenciesList, [
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
	
	public function hasListenersFor(string $eventName): bool {
		if (!isset($this->events[$eventName])) {
			return false;
		}
		return count($this->events[$eventName])>0;
	}
	
	public function getListenersFor($eventName): array {
		if (!array_key_exists($eventName, $this->events)) {
			return [];
		}
		return $this->events[$eventName];
	}
}