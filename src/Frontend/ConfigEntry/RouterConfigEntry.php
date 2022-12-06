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

	public function getConfig(string $instance): array {
		$routes = [];
		/** @var Route $route */
		foreach($this->router->getRouteCollection() as $name => $route) {
			if(!is_string($controller = $route->getDefault('_controller'))) continue;
			$controller = explode('::', $controller);
			if(class_exists($controller[0])) {
				$class = new \ReflectionClass($controller[0]);
				$attributes = $class->getAttributes(FrontendVisible::class);
			}
			if(isset($class) && !empty($controller[1])) {
				try {
					if(!empty($attrs = $class->getMethod($controller[1])->getAttributes(FrontendVisible::class))) {
						$attributes = $attrs;
					}
				} catch(\ReflectionException) {
					// method may not exist
				}
			}
			if($this->checkInstance($instance, $attributes ?? [])) {
				$routes[$name] = $route->getPath();
			}
		}
		return ['route' => $routes];
	}

	private function checkInstance(string $instance, array $attributes): bool {
		foreach($attributes as $attr) {
			$instances = $attr->getArguments()['instances'] ?? [];
			if(empty($instances) || in_array($instance, $instances)) {
				return true;
			}
		}
		return false;
	}
}
