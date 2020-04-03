<?php
namespace Webos;

use Exception;
use Webos\StringChar;
use Webos\Visual\Window;
use Webos\Exceptions\Collection\NotFound;
use Webos\Implementations\Web\Rendering;
use Webos\Stream\Content;
use salodev\Utils;
use salodev\FileSystem\File;

/**
 * Un VisualObject es subtipo de BaseObject porque puede ser representado
 * como un objeto de datos, o puede ser una representación visual de un
 * objeto de datos.
 **/
abstract class VisualObject extends BaseObject {
	
	protected /* @var int               */ $_objectID      = null;
	protected /* @var string            */ $_className     = null;
	protected /* @var self              */ $_parentObject  = null;
	protected /* @var Application       */ $_application   = null;
	protected /* @var ObjectsCollection */ $_childObjects  = null;
	protected /* @var EventsHandler     */ $_eventsHandler = null;

	public function __construct(Application $application, array $data = []) {
		$this->_application = $application;
		
		$this->checkRequiredParams($data);
		
		$data = array_merge($this->getInitialAttributes(), $data);

		parent::__construct($data);
		
		$this->_objectID = $this->generateObjectID();
		$this->index();
		$this->_eventsHandler = new EventsHandler();
		$this->_childObjects = new ObjectsCollection();
	}
	
	public function checkRequiredParams(array $params): void {
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

	final public function modified(): void {
		$this->getApplication()->triggerSystemEvent(
			'updateObject',
			$this,
			['object' => $this]
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
		return [];
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
		return str_replace(['Webos\Apps\\', 'Webos\Visual\Controls\\', '\\'], ['','','-'], get_class($this));
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
	final public function setObjectID(string $id): void {
		$this->_objectID = $id;
	}

	public function generateObjectID(): string {
		if (Webos::$development) {
			$index = $this->getApplication()->getWorkSpace()->getObjectIndex();
			return str_replace('\\','-', $this->getClassName()) . '-' . $index;
		} else {
			return str_replace('\\','-', $this->getClassName()) . '-' . md5(mt_rand()) . md5(microtime());
		}
	}
	
	/**
	 * Crea un objeto y lo agrega a la colección de hijos.
	 * @param string $className
	 * @param array $initialAttributes
	 * @return \Webos\VisualObject
	 */
	public function createObject(string $className, array $initialAttributes = []): self {
		$object = new $className($this->_application, $this, $initialAttributes);

		$this->getApplication()->triggerSystemEvent('createObject', $this, [
			'object' => $object,
		]);

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

		$this->getApplication()->triggerSystemEvent('createObject', $this, [
			'object' => $window,
		]);

		return $window;
	}

	//abstract public function getObjectByID($id);
	final public function getObjectByID(string $id, bool $horizontal = true): self {
		return $this->_childObjects->getObjectByID($id, $horizontal);
	}
	
	final public function hasObjectID(string $id): bool {
		try {
			// test if exists...
			$this->getObjectByID($id);
		} catch (NotFound $e) {
			return false;
		}
		
		return true;
	}
	
	final public function isDescendantOf(VisualObject $object): bool {
		try {
			$parent = $this->getParent();
		} catch (\TypeError $e) {
			return false;
		}
		if ($parent === $object) {
			return true;
		}
		return $parent->isDescendantOf($object);
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
	final public function setParentObject(self $object): self {
		$this->_parentObject = $object;

		$object->addChildObject($this);
		return $this;
	}

	public function addChildObject(self $child): self {
		$parent = $child->getParent();
		if (!($parent instanceof self)) {
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
	public function removeChild(self $child): self {
		$objectID = $child->getObjectID();
		$childs = $this->getChildObjects();
		$childs->removeObject($child);
		$child->unIndex();
		$this->getApplication()->triggerSystemEvent('removeObject', $this, [
			'objectId' => $objectID,
		]);
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
			$child->unIndex();
			$childsID[] = $child->getObjectID();
		}
		$childs->clear();
		foreach($childsID as $objectID) {
			$this->getApplication()->triggerSystemEvent('removeObject', $this, [
				'objectId' => $objectID,
			]);
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
		
		if (!$this->hasParent()) {
			throw new Exception('Current object has no parent object');
		}
		$parent = $this->getParent();
		if ($parent instanceof Window) {
			return $parent;
		} else {
			return $this->getParent()->getParentWindow();
		}
	}

	public function getParentByClassName(string $className): self {
		$parent = $this->_parentObject;
		if (!($parent instanceof self)) {
			return null;
		}
		
		if ($parent instanceof $className) {
			return $parent;
		} else {
			return $parent->getParentByClassName($className);
		}
	}

	public function action(string $name, array $params = []): void {
		
		$methodName = "action_{$name}";
		if (!method_exists($this, $methodName)) {
			throw new Exception("Action {$name} not allowed by " . get_class($this) . " object.");
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
		
		$this->$methodName($params);
	}
	
	public function action_scroll(array $params = []): void {
		$this->scrollTop  = $params['top' ] ?? 0;
		$this->scrollLeft = $params['left'] ?? 0;
		$this->triggerEvent('scroll');
	}

	public function bind(string $eventName, $eventListener, bool $persistent = true, array $contextData = []): self {
		$this->_eventsHandler->addListener($eventName, $eventListener, $persistent, $contextData);
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
	
	public function hasListenerFor(string $eventName): bool {
		return $this->_eventsHandler->hasListenersFor($eventName);
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
	
	public function getInlineStyleFromArray(array $attributes, bool $absolutize = true, bool $wrapped = true): string {
		
		$styles = [];

		$visualAttributesList = [
			'top',
			'left',
			'right',
			'bottom',
			'width',
			'height',
			'border',
			'borderRight',
			'borderLeft',
			'borderRadius',
			'textAlign',
			'backgroundImage',
			'backgroundColor',
			'backgroundSize',
			'overflowX',
			'overflowY',
			'marginTop',
			'marginBottom',
			'marginLeft',
			'marginRight',
			'margin',
			'fontSize',
			'fontWeight',
			'fontFamily',
			'padding',
			'color',
			'textDecoration',
			'position',
			'boxShadow',
			'cursor',
			'display',
		];

		foreach($visualAttributesList as $name) {
			
			$hyphenName = Utils::Hyphenize($name);
			
			$value = &$attributes[$name];
			if (!isset($value)) { 
				continue; 
			}
			if ($absolutize) {
				if (in_array($name, ['top', 'left', 'bottom', 'right'])) {
					$styles['position'] = 'absolute';
				}
			}

			if ($name == 'backgroundImage') {
				$value = "url({$value})";
			}
			if ($hyphenName=='border-radius') {
				$value = "{$value}px";
			}
			if ($hyphenName=='padding') {
				$value = "{$value}px";
			}
			if (strlen("{$value}")) {
				$styles[$hyphenName] = $value;
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
		
		if ($attributes['relative']??''=='vertical') {
			unset($styles['top']);
			$styles['position']='relative';
		}
		
		if (isset($attributes['rotate'])) {
			$styles['transform'] = "rotate({$attributes['rotate']}deg)";
		}
		
		$stylesString = self::getAsStyles($styles);
		
		if (!$wrapped) {
			return $stylesString;
		}

		if (strlen($stylesString)) {
			$ret = new StringChar(' style="__style_string__"');
			$ret->replace('__style_string__', $stylesString);
		} else {
			$ret = '';
		}

		return $ret;
		
	}
	
	public function getInlineStyle(bool $absolutize = true, bool $wrapped = true): string {
		$attributes = $this->getAttributes();
		return $this->getInlineStyleFromArray($attributes, $absolutize, $wrapped);
	}

	static public function getAsStyles(array $styles): string {
		$strings = [];
		foreach($styles as $name=>$value) {
			$unit = '';
			if (in_array($name, ['top','bottom','left','right','width','height','margin-top','font-size'])) {
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
	
	public function getPrevious(): self {
		return $this->getParent()->getChildObjects()->getPreviousTo($this);
	}
	
	public function getNext(): self {
		return $this->getParent()->getChildObjects()->getNextTo($this);
	}
	
	public function getLastChild(): self {
		return $this->getChildObjects()->getLastObject();
	}
	
	public function isHidden(): bool {
		if (!array_key_exists('visible', $this->_attributes)) {
			return false;
		}
		
		return !$this->visible;
	}
	
	public function isDisabled(): bool {
		if (!array_key_exists('disabled', $this->_attributes)) {
			return false;
		}
		
		return $this->disabled;
	}
	
	public function hide(): self {
		$this->unindex();
		$this->getApplication()->triggerSystemEvent('hideObject', $this, [
			'objectId' => $this->getObjectID(),
		]);
		$this->visible = false;
		return $this;
	}
	
	public function show(): self {
		$this->index();
		$this->getApplication()->triggerSystemEvent('showObject', $this, [
			'object' => $this,
		]);
		$this->visible = true;
		return $this;
	}
	
	public function disable(bool $value = true): self {
		$this->disabled = $value;
		return $this;
	}
	
	public function enable(bool $value = true): self {
		$this->disabled = !$value;
		return $this;
	}
	
	public function index(): self {
		$this->getApplication()->getWorkSpace()->indexObject($this);
		return $this;
	}
	
	public function unIndex(): self {
		$this->getApplication()->getWorkSpace()->unIndexObject($this);
		$this->getChildObjects()->unIndex();
		return $this;
	}

	// Media content for object.
	public function setFilePath(string $filePath): self {
		$this->setFile(new File($filePath));
		return $this;
	}
	
	public function setFile(File $file): self {
		$this->file = $file;
		$this->modified();
		return $this;
	}
	
	public function getFile(): File {
		return $this->file;
	}
	
	public function getMediaContent(array $parameters = []): Content {
		return Content::CreateFileContent($this->getFile()->getFullPath());
	}
	
	public function getMediaContentForSrc(bool $embed = false): string {
		if ($embed) {
			$file           = $this->getFile();
			$mimeType       = $file->getMimeType();
			$encodedContent = base64_encode($file->getAllContent());
			$src = "data:{$mimeType};base64, {$encodedContent}";
		} else {
			$src = 'getMediaContent?objectID=' . $this->getObjectID();
		}
		
		return $src;
	}
}