<?php
namespace Webos;
use Exception;
/**
 * Un VisualObject es subtipo de BaseObject porque puede ser representado
 * como un objeto de datos, o puede ser una representación visual de un
 * objeto de datos.
 **/
abstract class VisualObject extends BaseObject {

	protected $_objectID      = null;
	protected $_className     = null;
	protected $_parentObject  = null;
	protected $_childObjects  = null;
	protected $_events        = null;
	/**
	 *
	 * @var EventsHandler;
	 */
	protected $_eventsHandler = null;

	public function __construct(array $data = array()) {

		$initialAttributes = $this->getInitialAttributes();

		// Me aseguro que
		if (is_array($initialAttributes)) {
			$data = array_merge($this->getInitialAttributes(), $data);
		} else {
			throw new \Exception(__CLASS__ . '::getInitialAttributes() must return an array.');
		}

		parent::__construct($data);
		
		$this->_objectID = $this->generateObjectID();
		$this->_eventsHandler = new EventsHandler();
		$this->_childObjects = new ObjectsCollection();
	}

	final public function __get($name) {
		return parent::__get($name);
	}

	final public function __set($name, $value) {
		parent::__set($name, $value);
		$this->modified();
	}

	final public function modified() {
		$this->getParentApp()->triggerSystemEvent(
			'updateObject',
			$this,
			array('object' => $this)
		);
	}

	public function getChildObjects() {
		return $this->_childObjects;
	}

	/**
	 * Como es un objeto visual, necesitará de un 'renderizador'.
	 * Por el momento sólo se aplica para HTML.
	 */
	public function  getHTMLRendererName() {
		return '\Webos\Render\\' . str_replace('\\', '', get_class($this));
	}

	/**
	 * Perminte especificar un listado de atributos iniciales
	 * cuando se instancia un objeto sin especificarlos en el constructor.
	 *
	 * Si estos son especificados al construir el objeto, serán reemplazados.
	 */
	public function getInitialAttributes() {
		return array();
	}

	/**
	 * Hace posible identificar el tipo de objeto una vez exportado para conocer
	 * cual es su aspecto visual, u objeto de representación visual asociado.
	 **/
	final public function getClassName(){
		return get_class($this);
	}
	
	public function getClassNameForRender() {
		return str_replace(array('\Webos\Apps\\', '\Webos\Visual\Controls\\', '\\'), array('','','-'), get_class($this));
	}
	
	/**
	 * Hace posible la identificación única en el arbol de objetos.
	 **/
	final public function getObjectID(){
		return $this->_objectID;
	}
	
	/**
	 * Hace posible que su contenedor lo identifique de una manera mejor
	 * que por sí mismo.
	 **/
	final public function setObjectID($id){
		$this->_objectID = $id;
	}

	public function generateObjectID() {
		return str_replace('\\','-', $this->getClassName()) . '-' . md5(mt_rand()) . md5(microtime());
	}
	
	/**
	 * Crea un objeto y lo agrega a la colección de hijos.
	 * @param string $className
	 * @param array $initialAttributes
	 * @return \Webos\VisualObject
	 */
	public function createObject($className, array $initialAttributes = array()) {
		$object = new $className($this, $initialAttributes);

		$this->getParentApp()->triggerSystemEvent('createObject', $this, array(
			'object' => $object,
		));

		return $object;
	}

	//abstract public function getObjectByID($id);
	final public function getObjectByID($id, $horizontal = true) {
		return $this->_childObjects->getObjectByID($id, $horizontal);
	}

	final public function getObjectsByClassName($className) {
		return $this->_childObjects->getObjectsByClassName($className);
	}

	final public function getObjectsFromAttributes($params) {
		return $this->_childObjects->getObjectsFromAttributes($params);
	}

	/**
	 * Permite definir quién es su padre o contenedor.
	 * @param VisualObject $object
	 **/
	final public function setParentObject(VisualObject $object) {
		$this->_parentObject = $object;

		$object->addChildObject($this);
	}

	public function addChildObject(VisualObject $child) {
		$parent = $child->getParent();
		if (!($parent instanceof VisualObject)) {
			throw new Exception('Trying to add a child object without parent to ' . $this->getObjectID());
		}

		if ($parent->getObjectID() != $this->getObjectID()) {
			throw new Exception('Object id ' .
				$child->getObjectID() .
				'(' . get_class($child) . ') ' .
				'can not be child of ' . $this->getObjectId() .
				'(' . get_class($this) . ') '
			);
		}
		//echo 'Agregando ' . get_class($child) . ' a ' . get_class($this) . '<br />';
		$this->_childObjects->add($child);
	}
	
	/**
	 * 
	 * @param \Webos\VisualObject $child
	 * @return $this
	 */
	public function removeChild(VisualObject $child){
		$childs = $this->getChildObjects();
		$childs->removeObject($child);
		return $this;
	}
	
	/**
	 * 
	 * @return $this
	 */
	public function removeChilds() {
		$this->getChildObjects()->clear();
		return $this;
	}

	/**
	 * Permite obtener su padre o contenedor.
	 */
	final public function getParent() {
		return $this->_parentObject;
	}

	/**
	 *
	 * @return \Webos\Application 
	 */
	final public function getParentApp() {
		if ($this instanceof Application) {
			return $this;
		}

		$parent = $this->getParent();
		if ($parent instanceof Application) {
			return $parent;
		} else {
			return $this->getParent()->getParentApp();
		}
	}

	public function getParentWindow() {
		if ($this instanceof Visual\Window) return $this;

		$parent = $this->getParent();
		if (!($parent instanceof VisualObject)) return null;

		if ($parent instanceof Visual\Window) {
			return $parent;
		} else {
			return $this->getParent()->getParentWindow();
		}
	}

	public function getParentByClassName($className) {
		$parent = $this->getParent();
		if (!($parent instanceof VisualObject)) return null;
		
		if ($parent instanceof $className) {
			return $parent;
		} else {
			return $parent->getParentByClassName($className);
		}
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
	final public function getAttributes($name = null) {
		if ($name == null) return $this->_attributes;

		$attr = &$this->_attributes[$name];
		if (isset($attr)) return $attr;

		return null;
	}

	/* Todos los objetos VisualObject deben implementar IActionInvoker
	 * pero no necesariamente todos deben actuar en consecuencia. */
	public function action($name, array $params = null) {
		if (in_array($name,$this->getAllowedActions())){
			$this->$name($params);
		} else {
			throw new \Exception("Action $name not allowed by " . get_class($this) . " object.");
		}
		/*  */
	}

	/* IWithEvents */
	public function bind($eventName, $eventListener, $persistent = true) {
		if (in_array($eventName, $this->getAvailableEvents())) {
			$this->_eventsHandler->addListener($eventName, $eventListener, $persistent);
		} else {
			throw new \Exception("Event $eventName not available in " . get_class($this) . " object.");
		}
		return $this;
	}

	public function unbind($eventName) {
		$this->_eventsHandler->removeListeners($eventName);
		return $this;
	}

	/**
	 * 
	 * @param string $eventName
	 * @param array|null $params
	 * @return \Webos\EventsHandler
	 */
	public function triggerEvent($eventName, $params = null) {
		return $this->_eventsHandler->trigger($eventName, $this, $params);
	}
	
	/**
	 * Método genérico de reperesentación.
	 * @return string
	 */
	public function render() {
		$htmlChilds = $childObjects = $this->getChildObjects()->render();
		$html  = '';
		$html .= '<div>';
		$html .=	'<b>' . $this->getClassName() . '</b>';
		$html .=	'<div>' . $htmlChilds . '</div>';
		$html .= '</div>';
		return $html;
	}
	
	public function getInlineStyle($absolutize = true) {
		
		$attrs = $this->getAttributes();

		$styles = array();
		if (isset($attrs['top']) || isset($attrs['left'])) {
			$styles['position'] = 'absolute';
		}

		$visualAttributesList = array(
			'top',
			'left',
			'right',
			'bottom',
			'width',
			'height',
			'border',
			'text-align',
		);

		foreach($visualAttributesList as $name) {
			$value = &$attrs[$name];
			if (!isset($value)) { 
				continue; 
			}
			if ($absolutize) {
				if (in_array($name, array('top', 'left', 'bottom', 'right'))) {
					$styles['position'] = 'absolute';
				}
			}

			if (strlen("$value")) {
				$styles[$name] = $value;
			}
			
		}

		if (is_array($absolutize)) {
			$styles = array_merge($styles, $absolutize);
		} else {
			if ($absolutize === true) { 
				$styles['position'] = 'absolute';
			}
		}
		
		
		$styleStrings = array();
		foreach($styles as $attname=>$attvalue) {
			$styleStrings[] = "$attname:$attvalue";
		}

		if (count($styleStrings)) {
			$ret = new \Webos\String(' style="__style_string__"');
			$ret->replace('__style_string__', implode(';', $styleStrings));
		} else {
			$ret = '';
		}

		return $ret;
	}

	static public function getAsStyles(array $styles) {
		$strings = array();
		foreach($styles as $name=>$value) {
			$strings[] = $name . ':' . $value;
		}

		return implode(';', $strings);
	}
	
	/**
	 * El objeto sólo admite un conjunto de acciones.
	 **/
	public function getAllowedActions() {
		return array();
	}

	/**
	 * Debe definirse una lista de nombres de eventos disponibles
	 * @return array Lista de nombres de eventos disponibles.
	 */
	public function getAvailableEvents() {
		return array();
	}
}