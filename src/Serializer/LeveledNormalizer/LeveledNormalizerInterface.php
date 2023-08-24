<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Serializer\LeveledNormalizer;

interface LeveledNormalizerInterface {
	public function littleAdditionalData(mixed $object, array $parameter): array;
	public function normalAdditionalData(mixed $object, array $parameter): array;
	public function detailAdditionalData(mixed $object, array $parameter): array;
}
