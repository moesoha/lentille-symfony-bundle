<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeTimestampNormalizer implements NormalizerInterface {
	/**
	 * @param \DateTimeInterface $object
	 */
	public function normalize(mixed $object, string $format = null, array $context = []): int {
		return $object->getTimestamp();
	}

	public function getSupportedTypes(?string $format): array {
		return [\DateTimeInterface::class => true];
	}

	public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool {
		return $data instanceof \DateTimeInterface;
	}
}
