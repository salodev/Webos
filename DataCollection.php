<?php
namespace Webos;

/**
 * Cada elemento de esta colección es un array
 * pero será entregado modelado según el nombre de clase que se
 * especifique en su constructor.
 */
class DataCollection extends Collection{

	protected $_modelName;

	function __construct($data, $modelName){		
		$this->_modelName = $modelName;

		$this->_data = $this->getModelledData($data);
	}

	function setModelName($name) {
		$this->_modelName = $name;
	}

	function removeCurrent() {
		unset($this->_data[$this->_position]);
	}

	protected function getModelledData($data){
		
		if ($this->_modelName) {
			$modelName=$this->_modelName;
			$modelledData = array();
			foreach($data as $row) {
				$modelledData[] = new $modelName($row);
			}
			
			return $modelledData;
		}

		return $data;
	}

	function getFirstObject(){
		return $this->_data[0];
	}
}