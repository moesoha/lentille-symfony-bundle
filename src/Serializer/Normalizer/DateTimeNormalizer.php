<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Serializer\Normalizer;

use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsTaggedItem(index: 'datetime_normalizer')]
#[AutoconfigureTag(attributes: ['priority' => -700])]
class DateTimeNormalizer implements NormalizerInterface {
	/**
	 * @param \DateTimeInterface $object
	 */
	public function normalize(mixed $object, string $format = null, array $context = []): int {
		return $object->getTimestamp();
	}

	public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool {
		return $data instanceof \DateTimeInterface;
	}
}
