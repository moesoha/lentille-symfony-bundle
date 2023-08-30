<?php

namespace Lentille\SymfonyBundle\Frontend;

use Symfony\Component\HttpFoundation\Response;

interface FrontendRendererInterface {
	public function render(string $template, array $data = [], int $status = 200, array $headers = [], array $context = []): Response;
}
