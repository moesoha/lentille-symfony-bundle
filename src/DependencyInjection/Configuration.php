<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    public function getConfigTreeBuilder(): TreeBuilder {
        $builder = new TreeBuilder('lentille');
		$rootNode = $builder->getRootNode();

        $rootNode->children()
            ->arrayNode('exception')->addDefaultsIfNotSet()->children()
                ->arrayNode('show_trace_roles')
                ->defaultValue([])
                ->scalarPrototype()
            ->end()
        ->end();
		$rootNode->children()
			->arrayNode('frontend')->addDefaultsIfNotSet()->children()
				->arrayNode('instances') // TODO: check name
				->defaultValue(['main'])
				->scalarPrototype()
			->end()
		->end();

        return $builder;
    }
}
