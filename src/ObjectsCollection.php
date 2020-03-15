<?php

namespace Webos;

use Webos\Exceptions\Collection\NotFound;

/**
 * Each element of this collection is an Object
 */
class ObjectsCollection extends Collection {

	/**
	 * Only allows VisualObject instances
	 *
	 * @param VisualObject $object
	 **/
	function addObject(VisualObject $object): self {

		parent::add($object);
		return $this;
	}

	/**
	 * Rremoves object from collection
	 */
	function removeObject(VisualObject $objectToRemove): self {
		foreach($this as $object) {
			if ($object->getObjectID()==$objectToRemove->getObjectID()){
				parent::remove($this->key());
				break;
			}
		}
		return $this;
	}

	final public function getObjectByID(string $id, bool $horizontal = true): VisualObject {

		if ($horizontal) {
			foreach($this->_data as $object) {
				if ($object->getObjectID()==$id) {
					return $object;
				}
			}
		}

		foreach($this->_data as $object) {
			if ($object->getObjectID()==$id) {
				return $object;
			}
			try {
				return $object->getObjectByID($id, $horizontal);
			} catch (NotFound $e) {
				continue;
			}
		}
		
		throw new NotFound('Object Not Found');
	}

	function getObjectsByClassName(string $className): self {

		//return $inspector->getObjectsByClassName($className, $this);

		$list = new ObjectsCollection();
		foreach($this->_data as $object) {

			if ($object instanceof $className) {
				$list->add($object);
			}

			$list2 = $object->getObjectsByClassName($className);
			if (!empty($list2)) {
				$list->append($list2);
			}
		}

		return $list;
	}

	function getObjectsFromAttributes(array $params): self {
		$list = new ObjectsCollection();
		foreach($this->_data as $object) {
			foreach($params as $attName => $attValue) {
				if ($object->$attName==$attValue) {
					$list->add($object);
				}
			}

			if ($list2 = $object->getObjectsFromAttributes($params)){
				$list->append($list2);
			}
		}

		return $list;
	}

	/**
	 * Dispatch a message to each objects collection.
	 *
	 * @param string $name Objects collection method name.
	 * @param mixed $params Single parameter to send to all.
	 */
	function sendMessage(string $name, $params = null): void {
		foreach($this->_data as $object) {
			$object->$name($params);
		}
	}
	
	/**
	 * Render all objects and get its result as unique string
	 * @return string
	 */
	public function render(): string {
		$ret = '';
		foreach($this->_data as $object) {
			if (!$object->isHidden()) {
				$ret .= $object->render();
			}
		}
		return $ret;
	}
	
	public function getPreviousTo(VisualObject $object): VisualObject {
		foreach($this as $test) {
			if ($test===$object) {
				$newKey = $this->key() -1;
				if (!isset($this->_data[$newKey])) {
					throw new NotFound('No previous object');
				}
				return $this->_data[$newKey];
			}
		}
		throw new NotFound('No previous object');
	}
	
	public function getNextTo(VisualObject $object): VisualObject {
		foreach($this as $test) {
			if ($test===$object) {
				$newKey = $this->key() +1;
				if (!isset($this->_data[$newKey])) {
					throw new NotFound('No previous object');
				}
				return $this->_data[$newKey];
			}
		}
		throw new NotFound('No previous object');
	}
	
	public function getLastObject(): VisualObject {
		return parent::getLastObject();
	}
	
	public function unIndex(): self {
		foreach($this->_data as $object) {
			$object->unIndex();
		}
		return $this;
	}
	
	public function index(): self {
		foreach($this->_data as $object) {
			$object->index();
		}
		return $this;
	}
}