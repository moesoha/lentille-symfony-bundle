<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\DependencyInjection;

use Lentille\SymfonyBundle\Controller\ErrorController;
use Lentille\SymfonyBundle\Frontend\ConfigEntry\ExportableEnumEntry;
use Lentille\SymfonyBundle\Frontend\FrontendConfig;
use Lentille\SymfonyBundle\Frontend\FrontendInitialRendererInterface;
use Lentille\SymfonyBundle\Twig\TwigInitialRenderer;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class LentilleSymfonyExtension extends Extension implements PrependExtensionInterface {
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
			->setArgument('$warmupLocales', $config['warmup']['locales'])
		;

		$container
			->getDefinition(ExportableEnumEntry::class)
			->setArgument('$enumNamespaces', $config['frontend']['enum_namespaces'])
		;

		// $container->registerAttributeForAutoconfiguration(
		// 	AsExportableEnum::class,
		// 	static function (ChildDefinition $definition, AsExportableEnum $attribute): void {
		// 		var_dump($definition, $attribute);
		// 		$definition->addTag(AsExportableEnum::TAG);
		// 	}
		// );

		if($container->has('twig')) {
			$container->setAlias(FrontendInitialRendererInterface::class, TwigInitialRenderer::class);
		}
	}

	public function prepend(ContainerBuilder $container) {
		$container->prependExtensionConfig('framework', [
			'error_controller' => ErrorController::class
		]);
	}
}
