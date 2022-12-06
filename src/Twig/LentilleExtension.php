<?php

namespace Lentille\SymfonyBundle\Twig;

use Lentille\SymfonyBundle\Frontend\FrontendConfig;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class LentilleExtension extends AbstractExtension {
	public function __construct(
		private readonly FrontendConfig $frontendConfig,
		private readonly RequestStack $requestStack
	) {}

	public function getFunctions(): array {
		return [
			new TwigFunction('lentille_get_config', [$this, 'getConfig']),
			new TwigFunction('lentille_get_config_version', [$this, 'getConfigVersion'])
		];
	}

	public function getConfig(string $instance = 'main'): array {
		return $this->frontendConfig->getConfig($instance, $this->requestStack->getCurrentRequest()?->getLocale());
	}

	public function getConfigVersion(string $instance = 'main'): string {
		return $this->getConfig($instance)[0];
	}
}
