<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\DependencyInjection\Loader\Configurator;

use Lentille\SymfonyBundle\Frontend\FrontendInitialRendererInterface;
use Lentille\SymfonyBundle\Twig\TwigInitialRenderer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator) {
	$services = $configurator->services();
	$services
		->load('Lentille\\SymfonyBundle\\Twig\\', '../../Twig/*')
		->autowire()
		->autoconfigure()
	;
	$services->alias(FrontendInitialRendererInterface::class, TwigInitialRenderer::class);
};
