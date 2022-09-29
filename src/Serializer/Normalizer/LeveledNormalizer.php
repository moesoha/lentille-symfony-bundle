<?php declare(strict_types=1);

namespace Lentille\SymfonyBundle\Serializer\Normalizer;

use Lentille\SymfonyBundle\Serializer\LeveledNormalizer\LeveledNormalizerInterface;
use Lentille\SymfonyBundle\Serializer\LeveledNormalizer\NormalizeLevel;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

#[AsTaggedItem(priority: -600)]
class LeveledNormalizer implements NormalizerInterface, SerializerAwareInterface {
	use SerializerAwareTrait;

	public const CONTEXT_LEVEL_MAP = self::class . '+LevelMap';
	public const CONTEXT_PARAMETER = self::class . '+Parameter';

	/** @var array<class-string, LeveledNormalizerInterface> */
	private readonly array $normalizers;

	/**
	 * @param LeveledNormalizerInterface[] $normalizers
	 */
	public function __construct(
		#[TaggedIterator('lentille.serializer.leveled_normalizer', indexAttribute: 'index')] iterable $normalizers
	) {
		$this->normalizers = $normalizers instanceof \Traversable ? iterator_to_array($normalizers) : $normalizers;
	}

	public function normalize(mixed $object, string $format = null, array $context = []): mixed {
		$key = '[UnknownObject]';
		if(is_object($object)) {
			$normalizer = $this->normalizers[$key = $object::class];
		}
		if(empty($normalizer)) {
			throw new \RuntimeException('Cannot find normalizer for '.$key);
		}
		$param = $context[self::CONTEXT_PARAMETER][$key] ?? [];
		$level = $context[self::CONTEXT_LEVEL_MAP][$key] ?? NormalizeLevel::Little;
		if(!$level instanceof NormalizeLevel) {
			throw new \InvalidArgumentException('Bad normalize level');
		}
		$level = $level->value;
		$data = [];
		if($level & NormalizeLevel::MaskLittle) {
			$data += $normalizer->littleAdditionalData($object, $param);
		}
		if($level & NormalizeLevel::MaskNormal) {
			$data += $normalizer->normalAdditionalData($object, $param);
		}
		if($level & NormalizeLevel::MaskDetail) {
			$data += $normalizer->detailAdditionalData($object, $param);
		}
		return $this->serializer->normalize($data, $format, $context);
	}

	public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool {
		if(is_object($data)) {
			return array_key_exists($data::class, $this->normalizers);
		}
		return false;
	}
}
