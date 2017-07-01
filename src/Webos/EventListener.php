<?php
namespace Webos;
/**
 * Un observador de eventos genérico, que puede ser extendido
 * para agregar funcionalidades especiales.
 */
class EventListener {

	protected $_data = null;

	/**
	 * Al construir el observador, es posible preparar información
	 * que pueda utilizar en el momento que se invoque al método execute
	 * luego de producirse el evento.
	 *
	 * @param array $params Información que se quiera preparar.
	 */
	function __construct(array $params = array()) {
		$this->_data = $params;
	}

	/**
	 * Implementación del método execute.
	 * 
	 * @param object $source    Instancia del objeto que originó el evento.
	 * @param string $eventName Nombre del evento en cuestión.
	 * @param array $params    Información que se provee con el evento.
	 */
	public function execute($source, $eventName, $params) {
		if (!empty($this->_data['path']))
			require $this->_data['path'];
	}
}
