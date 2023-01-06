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
			/** @var FrontendVisible[] $singleAttrs */
			$singleAttrs = [];
			/** @var FrontendVisible[] $mergedAttrs */
			$mergedAttrs = [];

			if(!is_string($controller = $route->getDefault('_controller'))) continue;
			$controller = explode('::', $controller);
			if(class_exists($controller[0])) {
				$class = new \ReflectionClass($controller[0]);
				$singleAttrs = array_map(fn($a) => $a->newInstance(), $class->getAttributes(FrontendVisible::class));
				$mergedAttrs += $singleAttrs;
			}
			if(isset($class) && !empty($controller[1])) {
				try {
					if(!empty($attrs = $class->getMethod($controller[1])->getAttributes(FrontendVisible::class))) {
						$singleAttrs = array_map(fn($a) => $a->newInstance(), $attrs);
						$mergedAttrs += $singleAttrs;
					}
				} catch(\ReflectionException) {
					// method may not exist
				}
			}

			if($this->checkInstance($args->instance, $singleAttrs)) {
				$routes[$name] = $route->getPath();
				$attr = array_reduce(
					$mergedAttrs,
					fn(array $c, FrontendVisible $a) => array_merge($c, $a->attribute),
					[]
				);
				if(!empty($attr)) {
					$routesAttr[$name] = $attr;
				}
			}
		}
		return ['route' => $routes, 'routeAttr' => $routesAttr];
	}

	/**
	 * @param string $instance
	 * @param FrontendVisible[] $attributes
	 * @return bool
	 */
	private function checkInstance(string $instance, array $attributes): bool {
		foreach($attributes as $attr) {
			$instances = $attr->instances ?? [];
			if(empty($instances) || in_array($instance, $instances)) {
				return true;
			}
		}
		return false;
	}
}
