<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\DependencyInjection\Loader\Configurator;

use Lentille\SymfonyBundle\Serializer\LeveledNormalizer\LeveledNormalizerInterface;
use Lentille\SymfonyBundle\Serializer\Normalizer\DateTimeTimestampNormalizer;
use Lentille\SymfonyBundle\Serializer\Normalizer\LeveledNormalizer;
use Lentille\SymfonyBundle\Serializer\Normalizer\NormalizablePaginatorNormalizer;
use Lentille\SymfonyBundle\Serializer\Normalizer\PinNormalizeLevelNormalizer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator) {
	$services = $configurator->services();
	$services
		->set('serializer.normalizer.datetime_timestamp', DateTimeTimestampNormalizer::class)
		->tag('serializer.normalizer', ['priority' => -820])
	;
	$services
		->set('serializer.normalizer.lentille_paginator', NormalizablePaginatorNormalizer::class)
		->tag('serializer.normalizer', ['priority' => -820])
	;
	$services
		->set('serializer.normalizer.lentille_leveled', LeveledNormalizer::class)
		->tag('serializer.normalizer', ['priority' => -850])
		->autowire()
	;
	$services
		->set('serializer.normalizer.lentille_leveled_pinned', PinNormalizeLevelNormalizer::class)
		->tag('serializer.normalizer', ['priority' => -840])
	;
	$services
		->instanceof(LeveledNormalizerInterface::class)
		->tag('lentille.serializer.leveled_normalizer')
		->lazy()
	;
};
