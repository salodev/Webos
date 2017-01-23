<?php
namespace Webos;
/**
 * Cada elemento de esta colección es un objeto
 */
class ObjectsCollection extends Collection {

	/**
	 * Éste metodo se reescribe para admitir sólamente instancias de VisualObject.
	 *
	 * @param <type> $obj
	 **/
	function addObject(VisualObject $object) {

		parent::add($object);
		return $this;
	}

	/**
	 * Este método se reescribe para quitar de la colección una instancia de
	 * VisualObject de manera sencilla.
	 * Este método no destruye el objeto, sólo lo quita de la colección.
	 * 
	 * @param VisualObject      $objectToRemove Instancia del objeto a quitar de
	 *                                           la colección.
	 * @return ObjectsCollection Instancia de la colección.
	 */
	function removeObject(VisualObject $objectToRemove) {
		foreach($this as $object) {
			if ($object->getObjectID()==$objectToRemove->getObjectID()){
				parent::remove($this->key());
				break;
			}
		}
		/*$pointer = &$objectToRemove;
		unset($pointer);*/
		return $this;
	}

	final public function getObjectByID($id, $horizontal = true) {

		if ($horizontal) {
			foreach($this->_data as $object) {
				if ($object->getObjectID()==$id) return $object;
			}
		}

		foreach($this->_data as $object) {
			if (!($object instanceof VisualObject)) continue;
			if ($object->getObjectID()==$id) return $object;
			if ($test = $object->getObjectByID($id, $horizontal)){
				return $test;
			}
		}
	}

	function getObjectsByClassName($className, $inspector = null) {

		//return $inspector->getObjectsByClassName($className, $this);

		$list = new ObjectsCollection();
		foreach($this->_data as $object){
			/**
			 * Sólo VisualObjects implementan el método getClassName.
			 **/
			if (!($object instanceof VisualObject)) continue;

			if ($object instanceof $className) $list->add($object);

			$list2 = $object->getObjectsByClassName($className);
			if (!empty($list2)) $list->append($list2);
		}

		return $list;
	}

	function getObjectsFromAttributes($params) {
		$list = new ObjectsCollection();
		foreach($this->_data as $object) {

			if (!($object instanceof VisualObject)) continue;

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
	 * Este método envía un mensaje a todos los objetos de la collección.
	 * Dicho mensaje corresponde al nombre de un método esperado en todos
	 * los elementos de la colección que admitan a lo sumo un parámetro.
	 *
	 * @param string $name Nombre del método a invocar
	 * @param mixed $params Parámetros que se enviarán al método.
	 */
	function sendMessage($name, $params = null) {
		foreach($this->_data as $object) {
			$object->$name($params);
		}
	}
	
	/**
	 * Obtiene la lista de objetos renderizada.
	 * @return string
	 */
	public function render() {
		$ret = '';
		foreach($this->_data as $object) {
			$ret .= $object->render();
		}
		return $ret;
	}
}