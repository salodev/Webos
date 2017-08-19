<?php
namespace Webos;
/**
 * Se define un objeto base con características
 * generales.
 */
class BaseObject {

	protected $_attributes = array();

	public function __construct(array $data = array()) {
		if (!empty($data)) {
			$this->_attributes = $data;
		}
	}

	/**
	 * Método getter del objeto.
	 * Si existe una función cuyo nombre fuera
	 * __get_NOMBRE_ATRIBUTO, será invocada cuando
	 * se quiera acceder a un atributo NOMBRE_ATRIBUTO.
	 * 
	 * @param <type> $name
	 * @return <type>
	 */
	public function __get($name) {
		$getterMethod = '__get_' . $name;
		if (is_callable(array($this, $getterMethod))) {			
			return $this->$getterMethod();
		}
		
		if (isset($this->_attributes[$name])) {
			return $this->_attributes[$name];
		}

		return null;
	}

	/**
	 * Método setter del objeto.
	 * Si existe una función cuyo nombre fuera
	 * __set_NOMBRE_ATRIBUTO, será invocada cuando
	 * se establezca algún valor para NOMBRE_ATRIBUTO.
	 *
	 * @param <type> $name
	 * @param <type> $value
	 */
	public function __set($name, $value) {
		$setterMethod = '__set_' . $name;
		if (is_callable(array($this, $setterMethod))) {
			$tValue = $this->$setterMethod($value);

			// Si la función no retorna un valor,
			// se tomará el que entra en el parámetro
			if ($tValue !== null) {
				$value = $tValue;
			}
		}
		
		$orignalValue = null;
		if (array_key_exists($name, $this->_attributes)) {
			$orignalValue = $this->_attributes[$name];
		}

		$this->_attributes[$name] = $value;

		return $value !== $orignalValue;
	}

	/**
	 * Esta función verifica si un atributo existe
	 * en el objeto.
	 * @param <type> $name
	 * @return <type>
	 */
	public function isAttribute(string $name): bool {
		if (isset($this->_attributes[$name])) return true;

		return false;
	}

	/**
	 * Permite especificar sus atributos luego de haber sido construído
	 * el objeto
	 *
	 * @param array Atributos de la forma attributo=>valor
	 */
	final public function setAttributes(array $attributes) {
		$this->_attributes = array_merge($this->_attributes, $attributes);
	}

	/**
	 * Permite obtener un listado de atributos. Si se especifica el nombre
	 * se obtiene su valor, de lo contrario un array con todos sus
	 * atributos.
	 *
	 * @param string $name Nombre del atributo
	 */
	final public function getAttributes(string $name = null): array {
		if ($name == null) {
			return $this->_attributes;
		}

		$attr = &$this->_attributes[$name];
		if (isset($attr)) {
			return $attr;
		}

		return null;
	}

	public function getAttributesList(): array {
		return array_keys($this->_attributes);
	}

}