<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
	$routes->add('config', '/config/{instance}')
		->controller('Lentille\SymfonyBundle\Controller\FrontendController::configWithinInstanceAction')
	;
	$routes->add('config.default', '/config')
		->controller('Lentille\SymfonyBundle\Controller\FrontendController::configAction')
	;
};
