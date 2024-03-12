<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    public function getConfigTreeBuilder(): TreeBuilder {
        $builder = new TreeBuilder('lentille');
		$builder->getRootNode()
			->children()
				->arrayNode('exception')
					->addDefaultsIfNotSet()
					->children()
						->arrayNode('show_trace_roles')
							->defaultValue([])
							->scalarPrototype()
							->end()
						->end()
					->end()
				->end()
				->arrayNode('frontend')
					->addDefaultsIfNotSet()
					->children()
						->arrayNode('instances') // TODO: check name
							->defaultValue(['main'])
							->scalarPrototype()
							->end()
						->end()
						->arrayNode('instance_visible_to')
							->defaultValue([])
							->arrayPrototype()
								->scalarPrototype()
								->end()
							->end()
						->end()
						->arrayNode('enum_namespaces')
							->defaultValue([])
							->scalarPrototype()
							->end()
						->end()
					->end()
				->end()
				->arrayNode('warmup')
					->addDefaultsIfNotSet()
					->children()
						->arrayNode('locales') // TODO: check name
							->defaultValue([])
							->scalarPrototype()
							->end()
						->end()
					->end()
				->end()
			->end()
		;
        return $builder;
    }
}
