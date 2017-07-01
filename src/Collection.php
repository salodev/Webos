<?php

namespace Webos;
/**
 * Cada elemento de esta colección puede ser de cualquier tipo.
 */
class Collection implements \Iterator{
    protected $_position = 0;

    protected $_data = array();

    public function __construct(array $data = array()) {
        $this->_position = 0;
		$this->_data = $data;
    }

    public function rewind() {
		//reset($this->_data);
        $this->_position = 0;
    }

    public function current() {
		//return current($this->_data);
        return $this->_data[$this->_position];
    }

    public function key() {
		//return key($this->_data);
        return $this->_position;
    }

    public function next() {
		//next($this->_data);
        ++$this->_position;
    }

    public function valid() {
		//return !is_null(key($this->_data));
		return isset($this->_data[$this->_position]);
    }

	public function add($obj) {
		$this->_data[] = $obj;
	}

	public function append($objects) {
		foreach($objects as $object){
			$this->add($object);
		}
	}

	public function remove($key) {
		if (isset($this->_data[$key])) {
			/*$object = &$this->_data[$key];
			unset($object);*/
			unset($this->_data[$key]);
			//reset($this->_data);
			$this->_position=0;
		}
		
		// Fix indexes;
		$temp = array();
		foreach($this->_data as &$elem){
			$temp[] = $elem;
		}

		$this->_data = $temp;

	}

	public function clear() {
		$this->_data = array();
	}

	public function item($key) {
		if (isset($this->_data[$key]))
			return $this->_data[$key];

		return null;
	}

	public function count(){
		return count($this->_data);
	}
	
	public function length() {
		return count($this->_data);
	}

	public function getFirstObject(){
		return $this->_data[0];
	}

	/**
	 * Este método recorre cada elemento de la colección, e invocará a la
	 * función de Callback enviando como único parámetro el elemento
	 * de la iteración.
	 *
	 * A partir de la versión 5.3.0 de PHP se puede pasar como parámetro
	 * una función anónima.
	 *
	 * Ejemplo:
	 *
	 * $myCollection->each(function($object, $prevResult){
	 *     echo $object->getObjectID();
	 * });
	 *
	 * Más información en http://php.net/manual/functions.anonymous.php
	 *
	 * La declaración de la función debería ser como sigue:
	 *
	 * function ($object, $prevResult) {
	 *     // código de la función
	 * }
	 *
	 * Donde:
	 * $object     Es la instancia del objeto que se está recorriendo.
	 * $prevResult Es el valor de salida que dió la llamada a la función cuando
	 *             se recorría el objeto anterior. En la primer llamada a la
	 *             función de callback, el valor de $prevResult es null.
	 * returns     El retorno de la función será almacenado y enviado a la
	 *             llamada en la próxima iteración en el parámetro $prevResult.
	 *
	 * @param function $callback
	 */
	public function each(callable $callback) {
		$result = null;
		foreach($this->_data as $object) {
			$result = $callback($object, $result);
		}

		return $result;
	}
}