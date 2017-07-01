<?php
namespace Webos;

class ApplicationsCollection extends Collection {

	/**
	 * Este método se sobreescribe para permitir sólo instancias de
	 * objetos Application.
	 *
	 * @param Application $application
	 * @return <type>
	 */
	public function addApplication(Application $application) {
		parent::add($application);

		return $this;
	}

	public function getObjectById($id) {
		foreach($this->_data as $application) {
			$test = $application->getObjectById($id, true);

			if ($test && $test instanceof VisualObject) {
				return $test;
			}
		}
	}

	public function getObjectsByClassName($className) {

		$list = new ObjectsCollection();
		foreach($this->_data as $application) {
			$test = $application->getObjectsByClassName($className);			
			foreach($test as $object) {
				$list->add($object);
			}
		}

		return $list;
	}

	public function getVisualObjects() {
		$list = new ObjectsCollection();
		foreach($this->_data as $application) {
			$visualObjects = $application->getVisualObjects();

			$list->append($visualObjects);
		}
		return $list;
	}
}