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

	public function addListener($eventName, $eventListener, $persistent = true){

		$this->checkAvailableEvent($eventName);

		if (!($eventListener instanceof EventListener || is_callable($eventListener))) {
			throw new \Exception('eventListener must be a function or an EventListener instance');
		}
		
		$evData = new \stdClass();
		$evData->eventListener = $eventListener;
		$evData->persistent    = (bool) $persistent;

		$this->events[$eventName][] = $evData;

		return $this;
	}

	public function removeListeners($eventName){
		//@todo: completar esto..
		throw new \Exception('TODO: complete it.');
	}

	public function trigger($eventName, $source, $params = null) {

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

	private function checkAvailableEvent($eventName) {
		if ($this->useAvailableEvents) {
			if (!in_array($eventName, $this->availableEvents)) {
			 throw new \Exception("Unavailable {$eventName} event.");
			}
		}
	}

	private function call($eventListener, $source, $eventName, $params) {
		if (is_callable($eventListener)) {
			return $this->callFunction($eventListener, $source, $eventName, $params);
		} else {
			return $eventListener->execute($source, $eventName, $params);
		}
	}

	private function callFunction($fnc, $source, $eventName, $params) {
		if (is_array($fnc)) {
			if (isset($fnc[0], $fnc[1])) {
				$object = $fnc[0];
				$method = $fnc[1];

				return $object->$method($source, $eventName, $params);
			}
		}

		if (is_callable($fnc)) {
			return $fnc($source, $eventName, $params);
		}
	}

	public function setAvailableEvents(array $eventsList){
		$this->availableEvents = array_merge($this->availableEvents, $eventsList);
		$this->useAvailableEvents = true;
	}

	public function getAvailableEvents() {
		return $this->availableEvents;
	}
	
	public function hasListenersForEventName($eventName) {
		if (!isset($this->events[$eventName])) {
			return false;
		}
		return count($this->events[$eventName])>0;
	}
}