<?php
namespace Webos;

class EventsHandler {

	public $events = null;
	protected $useAvailableEvents = null;
	protected $availableEvents = null;

	public function __construct() {
		$this->events = array();
		$this->availableEvents = array();
		$this->useAvailableEvents = false;
	}

	public function addListener(string $eventName, callable $eventListener, bool $persistent = true): self {

		$this->checkAvailableEvent($eventName);

		if (!is_callable($eventListener)) {
			throw new \Exception('eventListener must be a function or an Closure instance');
		}
		
		if ($eventListener instanceof \Closure) {
			$eventListener = new Closure($eventListener);
		}
		
		$evData = new \stdClass();
		$evData->eventListener = $eventListener;
		$evData->persistent    = (bool) $persistent;

		$this->events[$eventName][] = $evData;

		return $this;
	}

	public function removeListeners(string $eventName){
		//@todo: completar esto..
		throw new \Exception('TODO: complete it.');
	}

	public function trigger(string $eventName, $source, $params = null): bool {

		$this->checkAvailableEvent($eventName);

		if(isset($this->events[$eventName])) {			
			foreach ($this->events[$eventName] as $k => $evData) {
				if (!$evData->persistent) {
					unset($this->events[$eventName][$k]);
				}
				$return = $this->call($evData->eventListener, $source, $eventName, $params);

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

	private function checkAvailableEvent(string $eventName) {
		if ($this->useAvailableEvents) {
			if (!in_array($eventName, $this->availableEvents)) {
			 throw new \Exception("Unavailable {$eventName} event.");
			}
		}
	}

	private function call(callable $eventListener, $source, $eventName, $params) {
		if (is_array($eventListener)) {
			if (isset($eventListener[0], $eventListener[1])) {
				$object = $eventListener[0];
				$method = $eventListener[1];

				return $object->$method($source, $eventName, $params);
			}
		}

		if (is_callable($eventListener)) {
			return $eventListener($source, $eventName, $params);
		}
	}

	public function setAvailableEvents(array $eventsList){
		$this->availableEvents = array_merge($this->availableEvents, $eventsList);
		$this->useAvailableEvents = true;
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
}