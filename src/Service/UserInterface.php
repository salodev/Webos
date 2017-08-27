<?php

namespace Webos\Service;

interface UserInterface {
	
	public function __construct(string $userName, string $applicationName, array $params = []);
	
	public function renderAll(): string;
	
	public function action(string $name, string $objectID, array $parameters, bool $ignoreUpdateObject = false): array;
	
	public function debug(): void;
}