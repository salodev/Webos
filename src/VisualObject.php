<?php
namespace Webos;

use Webos\StringChar;
use Webos\Visual\Window;
use Exception;
use Webos\Exceptions\Collection\NotFound;
use Webos\Implementations\Rendering;
use salodev\Utils;
use Webos\Stream\Content;

/**
 * Un VisualObject es subtipo de BaseObject porque puede ser representado
 * como un objeto de datos, o puede ser una representación visual de un
 * objeto de datos.
 **/
abstract class VisualObject extends BaseObject {

	protected $_objectID      = null;
	protected $_className     = null;
	protected $_parentObject  = null;
	protected $_application   = null;
	protected $_childObjects  = null;
	protected $_events        = null;
	/**
	 *
	 * @var EventsHandler;
	 */
	protected $_eventsHandler = null;

	public function __construct(Application $application, array $data = array()) {
		$this->_application = $application;
		
		$this->checkRequiredParams($data);
		
		$data = array_merge($this->getInitialAttributes(), $data);

		parent::__construct($data);
		
		$this->_objectID = $this->generateObjectID();
		$this->_eventsHandler = new EventsHandler();
		$this->_childObjects = new ObjectsCollection();
		
		$this->_eventsHandler->setAvailableEvents($this->getAvailableEvents());
	}
	
	public function checkRequiredParams(array $params) {
		$requiredParams = $this->getRequiredParams();
		foreach($requiredParams as $name) {
			if (!isset($params[$name])) {
				throw new Exception("Missing required param to initialize object: '{$name}'");
			}
		}
	}

	final public function __get($name) {
		return parent::__get($name);
	}

	final public function __set($name, $value) {
		if (parent::__set($name, $value)) {
			$this->modified();
		}
	}

	final public function modified() {
		$this->getApplication()->triggerSystemEvent(
			'updateObject',
			$this,
			array('object' => $this)
		);
	}

	public function getChildObjects(): ObjectsCollection {
		return $this->_childObjects;
	}

	/**
	 * Perminte especificar un listado de atributos iniciales
	 * cuando se instancia un objeto sin especificarlos en el constructor.
	 *
	 * Si estos son especificados al construir el objeto, serán reemplazados.
	 */
	public function getInitialAttributes(): array {
		return array();
	}
	
	/**
	 * Permite especificar parámetros obligatorios para inicializar el objeto
	 */
	public function getRequiredParams(): array {
		return [];
	}

	/**
	 * Hace posible identificar el tipo de objeto una vez exportado para conocer
	 * cual es su aspecto visual, u objeto de representación visual asociado.
	 **/
	final public function getClassName(): string {
		return static::class;
	}
	
	public function getClassNameForRender(): string {
		return str_replace(array('Webos\Apps\\', 'Webos\Visual\Controls\\', '\\'), array('','','-'), get_class($this));
	}
	
	/**
	 * Hace posible la identificación única en el arbol de objetos.
	 **/
	final public function getObjectID(): string{
		return $this->_objectID;
	}
	
	/**
	 * Hace posible que su contenedor lo identifique de una manera mejor
	 * que por sí mismo.
	 **/
	final public function setObjectID(string $id){
		$this->_objectID = $id;
	}

	public function generateObjectID(): string {
		return str_replace('\\','-', $this->getClassName()) . '-' . md5(mt_rand()) . md5(microtime());
	}
	
	/**
	 * Crea un objeto y lo agrega a la colección de hijos.
	 * @param string $className
	 * @param array $initialAttributes
	 * @return \Webos\VisualObject
	 */
	public function createObject(string $className, array $initialAttributes = array()): self {
		$object = new $className($this->_application, $this, $initialAttributes);

		$this->getApplication()->triggerSystemEvent('createObject', $this, array(
			'object' => $object,
		));

		return $object;
	}
	
	/**
	 * It is a so crazy idea! :D
	 * I need put a window content into another, e.g. into tabfolder.
	 * So... I need reuse these window. Just embeding it can be done.
	 * 
	 * @param string $windowClassName
	 * @param array $initialAttributes
	 * @return Window
	 */
	public function embedWindow(string $windowClassName, array $initialAttributes = []): Window {
		$window = new $windowClassName($this->_application, $initialAttributes, true);
		$window->setParentObject($this);

		$this->getApplication()->triggerSystemEvent('createObject', $this, array(
			'object' => $window,
		));

		return $window;
	}

	//abstract public function getObjectByID($id);
	final public function getObjectByID(string $id, bool $horizontal = true): self {
		return $this->_childObjects->getObjectByID($id, $horizontal);
	}
	
	final public function hasObjectID(string $id): bool {
		try {
			$object = $this->getObjectByID($id);
		} catch (NotFound $e) {
			return false;
		}
		
		return $object instanceof VisualObject;
	}

	final public function getObjectsByClassName(string $className): ObjectsCollection {
		return $this->_childObjects->getObjectsByClassName($className);
	}

	final public function getObjectsFromAttributes(array $params): ObjectsCollection {
		return $this->_childObjects->getObjectsFromAttributes($params);
	}

	/**
	 * Permite definir quién es su padre o contenedor.
	 * @param VisualObject $object
	 **/
	final public function setParentObject(VisualObject $object): self {
		$this->_parentObject = $object;

		$object->addChildObject($this);
		return $this;
	}

	public function addChildObject(VisualObject $child): self {
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
		return $this;
	}
	
	/**
	 * 
	 * @param \Webos\VisualObject $child
	 * @return $this
	 */
	public function removeChild(VisualObject $child): self {
		$objectID = $child->getObjectID();
		$childs = $this->getChildObjects();
		$childs->removeObject($child);
		$this->getApplication()->triggerSystemEvent('removeObject', $this, array(
			'objectId' => $objectID,
		));
		return $this;
	}
	
	/**
	 * 
	 * @return $this
	 */
	public function removeChilds(): self {
		$childs = $this->getChildObjects();
		$childsID = [];
		foreach($childs as $child) { 
			$childsID[] = $child->getObjectID();
		}
		$childs->clear();
		foreach($childsID as $objectID) {
			$this->getApplication()->triggerSystemEvent('removeObject', $this, array(
				'objectId' => $objectID,
			));
		}
		
		return $this;
	}

	/**
	 * Permite obtener su padre o contenedor.
	 * @return VisualObject
	 */
	final public function getParent(): self {
		return $this->_parentObject;
	}
	
	/**
	 * Permite obtener su padre o contenedor.
	 * @return VisualObject
	 */
	final public function hasParent(): bool {
		return $this->_parentObject instanceof self;
	}

	/**
	 *
	 * @return \Webos\Application 
	 */
	final public function getParentApp(): Application {
		return $this->_application;
	}
	
	final public function getApplication(): Application {
		return $this->_application;
	}

	public function getParentWindow(): Window {
		if ($this instanceof Window) {
			return $this;
		}

		$parent = $this->getParent();
		if (!($parent instanceof VisualObject)) {
			return null;
		}

		if ($parent instanceof Window) {
			return $parent;
		} else {
			return $this->getParent()->getParentWindow();
		}
	}

	public function getParentByClassName(string $className): VisualObject {
		$parent = $this->_parentObject;
		if (!($parent instanceof VisualObject)) {
			return null;
		}
		
		if ($parent instanceof $className) {
			return $parent;
		} else {
			return $parent->getParentByClassName($className);
		}
	}

	/* Todos los objetos VisualObject deben implementar IActionInvoker
	 * pero no necesariamente todos deben actuar en consecuencia. */
	public function action(string $name, array $params = []) {
		if (!in_array($name,$this->getAllowedActions())){
			throw new Exception("Action $name not allowed by " . get_class($this) . " object.");
		}
		
		if (
			$this->visible  === false
			||
			$this->disabled === true
			||
			$this->enabled  === false
		) {
			throw new Exception("Can not call action.");
		}
		
		$this->$name($params);
	}
	
	public function scroll(array $params = []): void {
		$this->scrollTop  = $params['top' ] ?? 0;
		$this->scrollLeft = $params['left'] ?? 0;
		$this->triggerEvent('scroll');
	}

	/* IWithEvents */
	public function bind(string $eventName, $eventListener, bool $persistent = true, array $contextData = []): self {
		if ($this->_eventsHandler->isAvailable($eventName)) {
			$this->_eventsHandler->addListener($eventName, $eventListener, $persistent, $contextData);
		} else {
			throw new Exception("Event $eventName not available in " . get_class($this) . " object.");
		}
		return $this;
	}

	public function unbind(string $eventName): self {
		$this->_eventsHandler->removeListeners($eventName);
		return $this;
	}

	/**
	 * 
	 * @param string $eventName
	 * @param array|null $params
	 * @return \Webos\EventsHandler
	 */
	public function triggerEvent(string $eventName, array $params = null) {
		return $this->_eventsHandler->trigger($eventName, $this, $params);
	}
	
	public function enableEvent(string $eventName): self {
		$this->_eventsHandler->enableEvent($eventName);
		return $this;
	}
	
	public function hasListenerFor(string $eventName): bool {
		return $this->_eventsHandler->hasListenersForEventName($eventName);
	}
	
	public function getEventsHandler(): EventsHandler {
		return $this->_eventsHandler;
	}
	
	/**
	 * Método genérico de reperesentación.
	 * @return string
	 */
	public function render(): string {
		return Rendering::Render($this);
		$htmlChilds = $childObjects = $this->getChildObjects()->render();
		$html  = '';
		$html .= '<div>';
		$html .=	'<b>' . $this->getClassName() . '</b>';
		$html .=	'<div>' . $htmlChilds . '</div>';
		$html .= '</div>';
		return $html;
	}
	
	public function getInlineStyleFromArray(array $attributes, bool $absolutize = true): string {

		$styles = array();
		if (isset($attributes['top']) || isset($attributes['left'])) {
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
			'textAlign',
			'backgroundImage',
			'backgroundColor',
			'overflowX',
			'overflowY',
			'marginTop',
			'marginBottom',
			'marginLeft',
			'marginRight',
			'margin',
		);

		foreach($visualAttributesList as $name) {
			
			$hyphenName = Utils::Hyphenize($name);
			
			$value = &$attributes[$name];
			if (!isset($value)) { 
				continue; 
			}
			if ($absolutize) {
				if (in_array($name, array('top', 'left', 'bottom', 'right'))) {
					$styles['position'] = 'absolute';
				}
			}

			if ($name == 'backgroundImage') {
				$value = "url({$value})";
			}
			if (strlen("$value")) {
				$styles[$hyphenName] = $value;
			}
			
		}

		if (is_array($absolutize)) {
			$styles = array_merge($styles, $absolutize);
		} else {
			if ($absolutize === true) { 
				$styles['position'] = 'absolute';
			}
		}
		
		if (!empty($attributes['position']) && $attributes['position'] == 'fixed') {
			$styles['position'] = 'fixed';
		}
		
		if (isset($styles['top']) && isset($styles['bottom'])) {
			if ($styles['top']==0 && $styles['bottom']==0) {
				unset($styles['height']);
			}
		}
		
		$stylesString = self::getAsStyles($styles);

		if (strlen($stylesString)) {
			$ret = new StringChar(' style="__style_string__"');
			$ret->replace('__style_string__', $stylesString);
		} else {
			$ret = '';
		}

		return $ret;
		
	}
	
	public function getInlineStyle(bool $absolutize = true): string {
		$attributes = $this->getAttributes();
		return $this->getInlineStyleFromArray($attributes, $absolutize);
	}

	static public function getAsStyles(array $styles): string {
		$strings = array();
		foreach($styles as $name=>$value) {
			$unit = '';
			if (in_array($name, ['top','bottom','left','right','width','height','margin-top'])) {
				$unit = 'px';
			}
			if (strpos($value, '%')!==false) {
				$unit = '';
			}
			$strings[] = "{$name}:{$value}{$unit}";
			unset($unit);
		}

		return implode(';', $strings);
	}
	
	/**
	 * El objeto sólo admite un conjunto de acciones.
	 **/
	public function getAllowedActions(): array {
		return array();
	}

	/**
	 * Debe definirse una lista de nombres de eventos disponibles
	 * @return array Lista de nombres de eventos disponibles.
	 */
	public function getAvailableEvents(): array {
		return array();
	}
	
	public function getPrevious() {
		return $this->getParent()->getChildObjects()->getPreviousTo($this);
	}
	
	public function getNext() {
		return $this->getParent()->getChildObjects()->getNextTo($this);
	}
	
	public function getLastChild(): VisualObject {
		return $this->getChildObjects()->getLastObject();
	}
	
	public function isHidden(): bool {
		if (!array_key_exists('hidden', $this->_attributes)) {
			return false;
		}
		
		return $this->hidden;
	}
	
	public function isDisabled(): bool {
		if (!array_key_exists('disabled', $this->_attributes)) {
			return false;
		}
		
		return $this->disabled;
	}
	
	public function hide() {
		$this->hidden = true;
	}
	
	public function show() {
		$this->hidden = false;
	}
	
	public function disable() {
		$this->disabled = true;
	}
	
	public function enable() {
		$this->disabled = false;
	}
	
	public function getMediaContent(array $parameters = []): Content {
		throw new \Exception('No content for this object');
	}
}