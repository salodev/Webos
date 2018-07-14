<?php

namespace Webos\Apps;
use Webos\Application;
use Webos\Implementations\Authentication;

class Auth extends Application {
	
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
			$test = Authentication::Login($formData['username'], $formData['password']);
			if (!$test) {
				$this->authWindow->messageWindow('Authentication failed!');
				return;
			}
			$_SESSION['username'] = $formData['username'];
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
			$test = Authentication::Register($formData);
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
	
}