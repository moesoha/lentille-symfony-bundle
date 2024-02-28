<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Frontend\ConfigEntry;

use Lentille\SymfonyBundle\Attribute\FrontendVisible;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class RouterConfigEntry implements ConfigEntryInterface {
	public function __construct(
		#[Autowire(service: 'router')] private readonly RouterInterface $router
	) {}

	public function getConfig(ConfigGetterArgs $args): array {
		$routes = [];
		$routesAttr = [];
		/** @var Route $route */
		foreach($this->router->getRouteCollection() as $name => $route) {
			$visible = false;
			$ownerInstance = null;
			$visibleInstances = [];
			$routeAttributes = [];

			if(!is_string($controller = $route->getDefault('_controller'))) continue;
			$controller = explode('::', $controller);
			if(class_exists($controller[0])) {
				$class = new \ReflectionClass($controller[0]);
				if($attr = ($class->getAttributes(FrontendVisible::class)[0] ?? null)?->newInstance()) {
					if($visible = !$attr->disable) {
						$ownerInstance = $attr->instance;
						$visibleInstances = $attr->visibleInstances;
						$routeAttributes = array_merge($routeAttributes, $attr->attribute);
					}
				}
			}
			if(isset($class) && !empty($controller[1])) {
				try {
					$attrs = $class->getMethod($controller[1])->getAttributes(FrontendVisible::class);
					if($attr = ($attrs[0] ?? null)?->newInstance()) {
						if($visible = !$attr->disable) {
							if($attr->instance) {
								$ownerInstance = $attr->instance;
							}
							if(!empty($attr->visibleInstances)) {
								$visibleInstances = $attr->visibleInstances;
							}
							$routeAttributes = array_merge($routeAttributes, $attr->attribute);
						}
					}
				} catch(\ReflectionException) {
					// method may not exist
				}
			}

			if(!$visible) continue;
			$ownerInstance ??= 'main';
			$isCurrentInstance = $args->instance === $ownerInstance;
			if(!$isCurrentInstance && !in_array($args->instance, $visibleInstances)) {
				continue;
			}

			$sysAttributes = [];
			if(!$isCurrentInstance) {
				$sysAttributes['instance'] = $ownerInstance;
			}
			$attrs = array_merge($sysAttributes, $routeAttributes);

			$routes[$name] = $route->getPath();
			if(!empty($attrs)) {
				$routesAttr[$name] = $attrs;
			}
		}
		return ['route' => $routes, 'routeAttr' => $routesAttr];
	}
}
