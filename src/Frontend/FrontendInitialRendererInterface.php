<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Frontend;

use Symfony\Component\HttpFoundation\Response;

interface FrontendInitialRendererInterface {
	public function render(
		string $template,
		array $data = [],
		int $status = 200,
		array $headers = [],
		array $context = []
	): Response;
}
