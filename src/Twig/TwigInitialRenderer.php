<?php

namespace Lentille\SymfonyBundle\Twig;

use Lentille\SymfonyBundle\Frontend\FrontendInitialRendererInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

class TwigInitialRenderer implements FrontendInitialRendererInterface {
	public function __construct(
		#[Autowire(service: 'twig')] private readonly TwigEnvironment $twig
	) {}

	public function render(
		string $template,
		array $data = [],
		int $status = 200,
		array $headers = [],
		array $context = []
	): Response {
		$twigView = $context['twigView'] ?? 'base.html.twig';
		return new Response($this->twig->render($twigView, $data), $status, $headers);
	}
}
