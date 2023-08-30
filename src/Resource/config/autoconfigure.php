<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\DependencyInjection\Loader\Configurator;

use Lentille\SymfonyBundle\Frontend\FrontendRenderer;
use Lentille\SymfonyBundle\Frontend\FrontendRendererInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator) {
	$services = $configurator->services();
	$services
		->load('Lentille\\SymfonyBundle\\Controller\\', '../../Controller/*')
		->lazy()
		->autowire()
		->autoconfigure()
	;
	$services
		->load('Lentille\\SymfonyBundle\\EventListener\\', '../../EventListener/*')
		->autowire()
		->autoconfigure()
	;
	$services
		->load('Lentille\\SymfonyBundle\\Form\\', '../../Form/*')
		->lazy()
		->autowire()
		->autoconfigure()
	;
	$services
		->load('Lentille\\SymfonyBundle\\Frontend\\', '../../Frontend/*')
		->lazy()
		->autowire()
		->autoconfigure()
	;
	$services->alias(FrontendRendererInterface::class, FrontendRenderer::class);
};
