<?php

namespace Webos\Implementations\Service\AuthService;

use Webos\Webos;
use Webos\Implementations\Service\LocalService\LocalService;
use Webos\WorkSpaceHandlers\Session as WorkSpaceHandler;

class AuthService extends LocalService {
	
	protected $_workSpaceHandler = WorkSpaceHandler::class;
	protected $userName = 'bearer';
	
	protected function getApplicationName(): string {
		return Webos::GetAuthApplicationName();
	}
	
	protected function getApplicationParams(): array {
		return Webos::GetAuthApplicationParams();
	}
	
	public function auth(string $userName, array $authParams): void {
		// nothing to do
	}
}