<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\DependencyInjection;

use Lentille\SymfonyBundle\Controller\ErrorController;
use Lentille\SymfonyBundle\Frontend\FrontendConfig;
use Lentille\SymfonyBundle\Frontend\FrontendInitialRendererInterface;
use Lentille\SymfonyBundle\Twig\TwigInitialRenderer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class LentilleSymfonyExtension extends Extension {
	public function getAlias(): string {
		return 'lentille';
	}

	public function load(array $configs, ContainerBuilder $container): void {
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
		$loader->load('services.yaml');

		$container
			->getDefinition(ErrorController::class)
			->setArgument('$traceRoles', $config['exception']['show_trace_roles'])
		;

		$container
			->getDefinition(FrontendConfig::class)
			->setArgument('$instances', $config['frontend']['instances'])
		;

		if($container->has('twig')) {
			$container->setAlias(FrontendInitialRendererInterface::class, TwigInitialRenderer::class);
		}
	}
}
