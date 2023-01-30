<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Serializer\Normalizer;

use Lentille\SymfonyBundle\Serializer\LeveledNormalizer\PinNormalizeLevel;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

#[AutoconfigureTag(attributes: ['priority' => -650])]
class PinNormalizeLevelNormalizer implements NormalizerInterface, SerializerAwareInterface {
	use SerializerAwareTrait;

	/**
	 * @param PinNormalizeLevel $object
	 */
	public function normalize(mixed $object, string $format = null, array $context = []): mixed {
		return $this->serializer->normalize($object->object, $format, array_merge($context, [
			LeveledNormalizer::CONTEXT_LEVEL_MAP => array_merge(
				$context[LeveledNormalizer::CONTEXT_LEVEL_MAP] ?? [],
				$object->levels
			),
			LeveledNormalizer::CONTEXT_PARAMETER => array_merge(
				$context[LeveledNormalizer::CONTEXT_PARAMETER] ?? [],
				$object->parameters
			)
		]));
	}

	public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool {
		return $data instanceof PinNormalizeLevel;
	}
}
