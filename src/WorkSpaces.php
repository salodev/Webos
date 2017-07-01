<?php
namespace Webos;

class WorkSpaces {

	static public function GetWorkSpace($username) {
		$file = self::OpenWorkspaceFile($username);		
		$ws = unserialize($file->getContent());
		return $ws['ws'];
	}

	static public function CreateWorkSpace($username) {
		$ws = new WorkSpace();
		self::StoreWorkSpace($username, $ws);
		return $ws;
	}

	static public function StoreWorkSpace($username, WorkSpace $ws) {		
		$file = self::OpenWorkspaceFile($username, 'create');
		$file->putContent(serialize($ws));
	}

	private function OpenWorkspaceFile($username, $option = 'readwrite') {
		$fh = new FileHandler();
		$path = Webos::GetConfig('paths/workspaces');
		$file = $fh->openFile($path . $username, $option);
		return $file;
	}
}
