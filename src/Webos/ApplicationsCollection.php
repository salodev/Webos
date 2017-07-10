<?php
namespace Webos;
use Webos\Exceptions\Collection\NotFound;

class ApplicationsCollection extends Collection {

	/**
	 * Este método se sobreescribe para permitir sólo instancias de
	 * objetos Application.
	 *
	 * @param Application $application
	 * @return <type>
	 */
	public function addApplication(Application $application): self {
		parent::add($application);

		return $this;
	}

	public function getObjectByID($id): VisualObject {
		foreach($this->_data as $application) {
			try {
				$test = $application->getObjectByID($id, true);
			} catch (NotFound $e) {
				continue;
			}

			if ($test && $test instanceof VisualObject) {
				return $test;
			}
		}
		throw new NotFound('Object not found');
	}

	public function getObjectsByClassName(string $className): ObjectsCollection {

		$list = new ObjectsCollection();
		foreach($this->_data as $application) {
			$test = $application->getObjectsByClassName($className);			
			foreach($test as $object) {
				$list->add($object);
			}
		}

		return $list;
	}

	public function getVisualObjects(): ObjectsCollection {
		$list = new ObjectsCollection();
		foreach($this->_data as $application) {
			$visualObjects = $application->getVisualObjects();

			$list->append($visualObjects);
		}
		return $list;
	}
	
	public function removeApplication(Application $applicationToRemove): self {
		foreach($this as $application) {
			if ($application===$applicationToRemove){
				parent::remove($this->key());
				break;
			}
		}
		return $this;
	}
}