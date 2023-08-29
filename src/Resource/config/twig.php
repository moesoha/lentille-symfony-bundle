<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator) {
	$services = $configurator->services();
	$services
		->load('Lentille\\SymfonyBundle\\Twig\\', '../../Twig/*')
		->autowire()
		->autoconfigure()
	;
};
