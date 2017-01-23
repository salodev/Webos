<?php
namespace Webos;

class SessionHandler {
	private $activeSession = null;

	public function __construct() {
		session_start();
		echo '<pre>'; debug_print_backtrace(); echo '</pre>';
	}

	public function getSession($sessionId) {
		// session_id($sessionId);

		$session = &$_SESSION['session'];
		if (isset($session)) {
			if ($session instanceof Session) {
				$this->activeSession = $session;
				return $session;
			}
		}

		return null;
	}

	public function getActiveSession() {
		return $this->activeSession;
	}
}