<?php
namespace Webos\Visual;
use Webos;
/**
 * Los ContainerObject están en el nivel mas alto de la estructura Composite,
 * en lo que se refiere a subclases de VisualObject.
 */
abstract class Container extends \Webos\VisualObject {

	final public function __construct(\Webos\Application $application, array $initialAttributes = array()) {
		parent::__construct($initialAttributes);
		$this->_parentObject = $application;
		$application->addChildObject($this);
		
		$this->preInitialize();

		$this->initialize($initialAttributes);
	}
	public function preInitialize() {}
	public function initialize() {}
}