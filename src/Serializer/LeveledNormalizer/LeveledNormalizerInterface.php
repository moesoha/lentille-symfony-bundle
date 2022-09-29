<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Serializer\LeveledNormalizer;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['lentille.serializer.leveled_normalizer'])]
interface LeveledNormalizerInterface {
	public function littleAdditionalData(mixed $object, array $parameter): array;
	public function normalAdditionalData(mixed $object, array $parameter): array;
	public function detailAdditionalData(mixed $object, array $parameter): array;
}
