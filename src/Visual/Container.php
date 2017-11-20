<?php
namespace Webos\Visual;
use Webos\VisualObject;
use Webos\Application;
/**
 * Los ContainerObject están en el nivel mas alto de la estructura Composite,
 * en lo que se refiere a subclases de VisualObject.
 */
abstract class Container extends VisualObject {
	
	protected $_embed = false;

	final public function __construct(Application $application, array $initialAttributes = array(), bool $embed = false) {
		parent::__construct($application, $initialAttributes);
		$this->_embed = $embed;
		if (!$embed) {
			$application->addChildObject($this);
		}
		
		$this->preInitialize();

		$this->initialize($initialAttributes);
	}
	public function preInitialize() {}
	public function initialize(array $params = []) {}
}