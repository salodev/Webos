<?php

namespace Webos\Service;

abstract class UserService {
	
	abstract public function __construct(string $userName, string $applicationName, array $params = []);
	
	abstract public function renderAll(): string;
	
	abstract public function action(string $name, string $objectID, array $parameters, bool $ignoreUpdateObject = false): array;
	
	abstract public function debug(): void;
	
	public function getOutputStream(): array {
		return [];
	}
	
	public function getFilestoreDirectory(): string {
		return '';
	}
}