<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Serializer\Normalizer;

use Lentille\SymfonyBundle\Paginator\NormalizablePaginatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class NormalizablePaginatorNormalizer implements NormalizerInterface, SerializerAwareInterface {
	use SerializerAwareTrait;

	/**
	 * @param NormalizablePaginatorInterface $object
	 */
	public function normalize(mixed $object, string $format = null, array $context = []): array {
		return [
			'perPage' => $object->getPerPage(),
			'count' => $object->getCount(),
			'result' => $this->serializer->normalize($object->getResult(), $format, $context)
		];
	}

	public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool {
		return $data instanceof NormalizablePaginatorInterface;
	}
}
