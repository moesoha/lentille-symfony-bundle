<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Controller;

use Lentille\SymfonyBundle\Frontend\FrontendConfig;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

// #[Route('/_fe', name: 'frontend.')]
#[AsController]
class FrontendController {
	#[Route('/config/{instance}', name: 'config')]
	public function configWithinInstanceAction(string $instance, FrontendConfig $config): Response {
		[$version, $data] = $config->getConfig($instance);
		return new JsonResponse($data + ['_version' => $version]);
	}

	#[Route('/config', name: 'config.default')]
	public function configAction(FrontendConfig $config): Response {
		return $this->configWithinInstanceAction('main', $config);
	}
}
