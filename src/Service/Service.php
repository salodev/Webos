<?php

namespace Webos\Service;

abstract class Service {
	
	protected $_user              = null;
	public    $_applicationName   = [];
	public    $_applicationParams = [];
	public    $_metadata          = [];
	protected $_interface         = null;
	protected $_system            = null;
	
	public function __construct(string $userName, string $applicationName, array $applicationParams = [], array $metadata = []) {
		
	}
	
	abstract public function renderAll(): string;
	
	abstract public function action(string $name, string $objectID, array $parameters, bool $ignoreUpdateObject = false): array;
	
	abstract public function getOutputStream(): array;
	
	abstract public function getMediaContent(string $objectID, array $params = []): array;
	
	abstract public function getFilestoreDirectory(): string;
	
	abstract public function debug(): void;
	
	abstract public function setViewportSize(int $width, int $height): void;
}