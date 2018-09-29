<?php

namespace Webos\FrontEnd;

interface PageWrapper {
	public function setContent(string $html): PageWrapper;
	public function getHTML(): string;
}