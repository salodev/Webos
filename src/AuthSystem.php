<?php
namespace Webos;

class AuthSystem {

	private $_system = null;

	final public function __construct(System $system) {
		$this->_system = $system;
	}
	public function authUser($username, $password) {
		if ($username == 'salojc2006' && $password == '321321') {
			$session = new Session(new User(array(
				'username' => 'salojc2006',
				'name' => 'Salomon',
			)));
			return $session;
		}
		return null;
	}

	public function getWorkSpace(User $user) {
		$file = $this->openWorkspaceFile($user);		
		$ws = unserialize($file->getContent());
		return $ws['ws'];
	}

	public function createWorkSpace(User $user) {
		$ws = new WorkSpace();
		$ws->setSystemEnvironment($this->_system);
		$this->storeWorkSpace($user, $ws);
		return $ws;
	}

	public function storeWorkSpace(User $user, WorkSpace $ws) {		
		$file = $this->openWorkspaceFile($user, 'create');
		$arr = array(
			'ws' => $ws,
		);
		$file->putContent(serialize($arr));
	}

	private function openWorkspaceFile(User $user, $option = 'readwrite') {
		$fh = new FileHandler();
		$path = $this->_system->getConfig('paths/workspaces');
		$file = $fh->openFile($path . $user->username, $option);
		return $file;
	}
}
