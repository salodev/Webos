<?php
namespace Webos;
class Session {
	private $systemEnvironment = null;
	private $user = null;
	private $sessionId = null;

	public function __construct(User $user) {

		// session_regenerate_id(true);

		$this->sessionId = session_id();
		$this->user = $user;
		$this->storePersistentData('session', $this);
	}

	public function getSessionId() {
		return $this->sessionId;
	}

	public function getUser() {
		return $this->user;
	}

	public function getUser2() {
		return $this->user;
		die("entra aca");
	}

	public function setSystemEnvironment($system) {
		$this->systemEnvironment = $system;
		return $this;
	}

	public function getSystemEnvironment() {
		return $this->systemEnvironment;
	}

	public function setWorkSpace($workSpace) {
		$this->storePersistentData('workSpace', $workSpace);
		return $this;
	}

	public function getWorkSpace() {
		return $this->getPersistentData('workSpace');
	}

	private function storePersistentData($name, $data) {
		$_SESSION[$name] = $data;
	}

	private function getPersistentData($name) {
		if (isset($_SESSION[$name])) return $_SESSION[$name];
	}
}

