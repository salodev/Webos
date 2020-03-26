<?php

namespace Webos\Apps;

use Webos\Webos;
use Webos\Implementations\Authentication;

class UserPassAuthApp extends AuthApplication {
	
	static private $_loginFn;
	static private $_registerFn;
	
	public function main(array $data = []): void {
		$w = $this->openWindow();
		$this->authWindow = $w;
		
		$w->showControls = false;
		$w->title = 'Please identify';
		$w->width = 380;
		
		$t = $w->createTabsFolder();
		
		$tlogin = $t->addTab('Login');
		$this->tlogin = $tlogin;
		$u = $tlogin->createTextBox('Username', 'username', ['width'=>200, 'labelWidth' => 130]);
		$tlogin->createPasswordBox('Password', 'password');
		
		$tlogin->createWindowButton('Login')->onClick(function() {
			$formData = $this->tlogin->getFormData();
			if (empty($formData['username'])) {
				throw new \Exception('Please, provide the username');
			}
			if (empty($formData['password'])) {
				throw new \Exception('Please, provide the password');
			}
			$test = Webos::Authorize($formData['username'], ['password' => $formData['password']]);
			$this->getWorkSpace()->getSystemEnvironment()->triggerEvent('loggedIn', $this);
			$this->finish();
		});
		
		$treg = $t->addTab('Register');
		$this->treg = $treg;
		$treg->createTextBox('Username', 'username', ['width'=>200, 'labelWidth' => 130]);
		$treg->createPasswordBox('Password', 'password');
		$treg->createPasswordBox('Repeat Password', 'password2');
		$treg->createTextBox('Email', 'email');
		$treg->createTextBox('Repeat Email', 'email2');
		
		$treg->createWindowButton('Register')->onClick(function() {
			$formData = $this->treg->getFormData();
			$test = Webos::Register($formData);
			if (!$test) {
				$this->authWindow->messageWindow('Register failed!');
				return;
			}
			$message = 'Your account was successfuly created. Try to login now. Your account may require admin approval, so you may need wait for it.';
			$this->authWindow->messageWindow($message)->onClose(function() {
				$params = $this->getParams();
				$ws = $this->getWorkSpace();
				$ws->startApplication(static::class, $params);
				$this->finish();
			});
		});
		
		$w->height = 230;
		
		
		$tlogin->onSelect(function() {
			$this->authWindow->height = 230;
		});
		
		$treg->onSelect(function() {
			$this->authWindow->height = 320;
		});
		
		$u->focus();
	}
	
	public function getProvider(): string {
		return 'Webos kit';
	}
	
	public function getName(): string {
		return 'Auth Application';
	}
	
	public function getVersion(): string {
		return '0.0.1';
	}
	
	static public function SetLoginFn(callable $fn): void {
		self::$_loginFn = $fn;
	}
	
	static public function SetRegisterFn(callable $fn): void {
		self::$_registerFn = $fn;
	}
	
	static public function Authenticate($username, array $authParams): void {
		if (!is_callable(self::$_loginFn)) {
			$class = static::class;
			throw new Exception("No login FN registered. Use {$class}::SetLoginFn() to set a callback authenticator or write your own Application by Webos\Apps\AuthApplication or Webos\Apps\UserPassAuthAppp classes extending.");
		}
		$fn = self::$_loginFn;
		$fn($username, $authParams);
	}
	
	static public function Register(array $params = []): bool {
		if (!is_callable(self::$_registerFn)) {
			throw new Exception('No register FN registered');
		}
		$fn = self::$_registerFn;
		return $fn($params);
	}

}