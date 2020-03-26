<?php

namespace Webos\Implementations\Service;

interface Service {
	
	public function renderAll(): string;
	
	public function action(string $name, string $objectID, array $parameters, bool $ignoreUpdateObject = false): array;
	
	public function getOutputStream(): array;
	
	public function getMediaContent(string $objectID, array $params = []): array;
	
	public function getFilestoreDirectory(): string;
	
	public function debug(): void;
	
	public function setViewportSize(int $width, int $height): void;
	
	public function auth(string $userName, array $authParams): void;
}