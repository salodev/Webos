<?php

namespace Webos\Implementations;
use Webos\VisualObject;
use Exception;

class Rendering {
	
	private static $_fn;
	
	static public function SetFn(callable $fn): void {
		self::$_fn = $fn;
	}
	
	static public function Render(VisualObject $object): string {
		if (!self::$_fn) {
			throw new Exception('No render callback found');
		}
		$fn = self::$_fn;
		return $fn($object);
	}
}